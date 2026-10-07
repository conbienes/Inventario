<?php

namespace App\Services\BonoRegalo;

use App\Models\BonoRegalo\ClienteBonoR;
use App\Services\BusinessCentralService;
use Illuminate\Support\Facades\Log;

/**
 * Sincroniza un cliente de Bono Regalo con Business Central:
 * cliente (customersExt) + valor de dimensión TERCERO.
 *
 * Es idempotente: si el cliente ya tiene bc_system_id no se vuelve a crear en BC,
 * solo se reintenta lo que falte (p. ej. la dimensión).
 */
class ClienteBCSyncService
{
    public function __construct(private BusinessCentralService $bc)
    {
    }

    /**
     * @return array{ok: bool, ok_customer: bool, ok_dimension: bool, error: ?string}
     */
    public function sincronizar(ClienteBonoR $c): array
    {
        $companyId = config('services.bc.company_id');
        if (!$companyId) {
            return $this->guardar($c, false, false, 'Falta configurar BC_COMPANY_ID');
        }

        $nombre = $this->nombreMostrar($c);

        $customer = $c->bc_system_id ? ['ok' => true] : $this->crearCustomer($c, $companyId, $nombre);
        $dimension = $this->crearDimension($c, $companyId, $nombre);

        $error = collect([$customer['error'] ?? null, $dimension['error'] ?? null])->filter()->implode(' | ') ?: null;

        return $this->guardar($c, $customer['ok'], $dimension['ok'], $error, $customer['final'] ?? null);
    }

    public static function esEmpresa(ClienteBonoR $c): bool
    {
        return in_array($c->tipo_documento, ['NIT', 'NIT de otro país'], true);
    }

    public function nombreMostrar(ClienteBonoR $c): string
    {
        if (self::esEmpresa($c)) {
            return trim((string) $c->razons);
        }

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
            $c->nombre, $c->segundo_nombre, $c->apellidos, $c->segundo_apellido,
        ]))));
    }

    private function crearCustomer(ClienteBonoR $c, string $companyId, string $nombre): array
    {
        try {
            $esEmpresa = self::esEmpresa($c);
            $correo = strtolower((string) $c->correo);

            $payload = array_filter([
                'No' => $c->cedula,
                'NombreCompleto' => $nombre ?: null,
                'Correo' => $correo ?: null,
                'CorreoFE' => $correo ?: null,
                'Tipoidentificacion' => $c->tipo_documento ?: null,
                'TipoContribuyente' => $esEmpresa ? 'Persona Jurídica' : 'Persona Natural',
                'AreaImpuesto' => $c->tipoActividad, // "CLI-NRI" / "CLI-RI"
                'PrimerNombre' => $esEmpresa ? null : ($c->nombre ?: null),
                'SegundoNombre' => $esEmpresa ? null : ($c->segundo_nombre ?: null),
                'PrimerApellido' => $esEmpresa ? null : ($c->apellidos ?: null),
                'SegundoApellido' => $esEmpresa ? null : ($c->segundo_apellido ?: null),
                'RazonSocial' => $esEmpresa ? ($c->razons ?: null) : null,
            ], fn($v) => $v !== null && !(is_string($v) && trim($v) === ''));

            // CREATE (sin NumeroIdentificacion) y luego PATCH con NumeroIdentificacion
            $created = $this->bc->createCustomerExt($companyId, $payload);
            $systemId = $created['systemId'] ?? ($created['SystemId'] ?? null);

            if (!$systemId) {
                return ['ok' => false, 'error' => 'BC no devolvió systemId al crear el cliente'];
            }

            $patched = !empty($c->cedula)
                ? $this->bc->updateCustomerExt($companyId, $systemId, ['NumeroIdentificacion' => (string) $c->cedula])
                : null;

            // Si el PATCH devolvió el objeto completo lo preferimos (trae el etag actualizado)
            $final = is_array($patched) && isset($patched['@odata.etag']) ? $patched : $created;

            return ['ok' => true, 'final' => $final];
        } catch (\Throwable $e) {
            Log::warning("BC: no se pudo crear el cliente {$c->id}: " . $e->getMessage());

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function crearDimension(ClienteBonoR $c, string $companyId, string $nombre): array
    {
        try {
            $payload = array_filter([
                'DimensionCode' => config('services.bc.dim_tercero'),
                'Code' => mb_substr((string) ($c->cedula ?: $c->id), 0, 50),
                'Name' => mb_substr($nombre, 0, 100),
            ], fn($v) => $v !== null && trim((string) $v) !== '');

            $this->bc->upsertDimensionValue($companyId, $payload);

            return ['ok' => true];
        } catch (\Throwable $e) {
            Log::warning("BC: no se pudo crear la dimensión del cliente {$c->id}: " . $e->getMessage());

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function guardar(ClienteBonoR $c, bool $okCustomer, bool $okDimension, ?string $error, ?array $final = null): array
    {
        if ($final) {
            $c->bc_system_id = $final['systemId'] ?? ($final['SystemId'] ?? $c->bc_system_id);
            $c->bc_etag = $final['@odata.etag'] ?? $c->bc_etag;
        }

        // Solo queda "cargado" cuando cliente y dimensión están en BC
        $c->cargado = $okCustomer && $okDimension ? 1 : 0;
        $c->bc_synced_at = now();
        $c->bc_error = $error ? mb_strimwidth($error, 0, 1000, '…') : null;
        $c->save();

        return [
            'ok' => (bool) $c->cargado,
            'ok_customer' => $okCustomer,
            'ok_dimension' => $okDimension,
            'error' => $error,
        ];
    }
}
