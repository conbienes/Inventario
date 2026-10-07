<?php

namespace App\Services\BonoRegalo;

use App\Models\BonoRegalo\FacturaBonoR;
use App\Services\BusinessCentralService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Envía una factura de Bono Regalo a Business Central como asiento de diario:
 *  - una línea DETALLE (negativa) por tarjeta vendida
 *  - una línea TOTAL (positiva) contra la cuenta de banco
 *
 * Garantías:
 *  - Lock por factura: dos peticiones/jobs no envían la misma factura a la vez.
 *  - Una factura ENVIADO nunca se reenvía.
 *  - Idempotencia por línea (tabla bc_lineas_enviadas): al reintentar una factura en ERROR
 *    solo se envían las líneas que BC no aceptó antes.
 */
class FacturaBCService
{
    public const RELACIONES = ['cliente:id,cedula', 'items:id,factura_id,tarjeta_id,precio_unitario', 'items.tarjeta:id,numero', 'payments.method:id,tipo'];

    public function __construct(private BusinessCentralService $bc)
    {
    }

    /**
     * @return array Resultado para la respuesta de la pantalla de Contabilidad.
     *               'omitida' => true si ya estaba enviada o en proceso.
     */
    public function enviar(FacturaBonoR $f): array
    {
        $lock = Cache::lock("bc:factura:{$f->id}", 600);
        if (!$lock->get()) {
            return ['factura_id' => $f->id, 'omitida' => true, 'motivo' => 'en proceso'];
        }

        try {
            // Releer el estado con el lock tomado: otra petición pudo enviarla mientras tanto
            if (FacturaBonoR::whereKey($f->id)->value('estado_bc') === 'ENVIADO') {
                return ['factura_id' => $f->id, 'omitida' => true, 'motivo' => 'ya enviada'];
            }

            $f->loadMissing(self::RELACIONES);

            return $this->enviarLineas($f);
        } finally {
            $lock->release();
        }
    }

    private function enviarLineas(FacturaBonoR $f): array
    {
        $companyId = config('services.bc.company_id');
        $cfg = config('services.bc.bono_regalo');
        $facturaCode = $f->numero_factura ?: 'BR' . $f->id;
        $fecha = $f->fecha ? Carbon::parse($f->fecha)->toDateString() : now()->toDateString();
        $tercero = (string) ($f->cliente->cedula ?? '');

        if (!$companyId) {
            return $this->registrar($f, ['Falta configurar BC_COMPANY_ID'], $facturaCode, $fecha, $tercero, 0, 0);
        }
        if ($tercero === '') {
            return $this->registrar($f, ['Factura sin cédula de cliente'], $facturaCode, $fecha, $tercero, 0, 0);
        }

        $items = $f->items->filter(fn($i) => $i->tarjeta);
        $valorTotal = (float) $items->sum('precio_unitario'); // lo cobrado, no el valor nominal
        $yaEnviadas = DB::table('bc_lineas_enviadas')->where('factura_id', $f->id)->pluck('clave')->flip();

        $base = [
            'Diario' => $cfg['diario'],
            'Seccion' => $cfg['seccion'],
            'TipoDocumento' => $cfg['tipo_documento'],
            'Factura' => $facturaCode,
            'Fecha_Registro' => $fecha,
            'Tercero' => $tercero,
            'LibroCodigo' => $cfg['libro_codigo'],
            'CodigoConcepto' => $cfg['codigo_concepto'],
            'GrupoImpuesto' => $cfg['grupo_impuesto'],
            'AreaImpuesto' => $cfg['area_impuesto'],
            'DocumentoExterno' => $facturaCode,
        ];

        $errores = [];

        // Líneas DETALLE (negativas), una por tarjeta
        foreach ($items as $item) {
            $clave = "DET:{$item->tarjeta_id}";
            if ($yaEnviadas->has($clave)) {
                continue;
            }

            $importe = -1 * (float) $item->precio_unitario;
            $error = $this->enviarLinea($f, $clave, $importe, $companyId, $base + [
                'TerceroTipo' => 'G/L Account',
                'NumeroCuenta' => $cfg['cta_detalle'],
                'Description' => "TarjetaBR N {$item->tarjeta->numero} (Fac {$facturaCode})",
                'Importe' => $importe,
            ]);
            if ($error) {
                $errores[] = $error;
            }
        }

        // Línea TOTAL (positiva), una por factura
        if (!$yaEnviadas->has('TOTAL')) {
            $numeros = $items->map(fn($i) => $i->tarjeta->numero)->filter();
            $descTotal = mb_strimwidth("Fac {$facturaCode}" . ($numeros->isNotEmpty() ? '(T: ' . $numeros->implode(', ') . ')' : ''), 0, 100, '…');

            $error = $this->enviarLinea($f, 'TOTAL', $valorTotal, $companyId, $base + [
                'TerceroTipo' => 'Bank Account',
                'NumeroCuenta' => $cfg['cta_total'],
                'Description' => $descTotal,
                'Importe' => $valorTotal,
                'CodigoAuditoria' => $this->metodoPago($f),
            ]);
            if ($error) {
                $errores[] = $error;
            }
        }

        return $this->registrar($f, $errores, $facturaCode, $fecha, $tercero, $items->count(), $valorTotal);
    }

    /** Envía una línea; si BC la acepta queda registrada para no repetirla. Devuelve el error o null. */
    private function enviarLinea(FacturaBonoR $f, string $clave, float $importe, string $companyId, array $payload): ?string
    {
        try {
            $resp = $this->bc->createCustomJournalLine($companyId, $payload);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }

        $falla = ($resp['ok'] ?? null) === false
            || (int) ($resp['status'] ?? 0) >= 400
            || !empty($resp['error'])
            || !empty($resp['data']['error']['message']);

        if ($falla) {
            return $resp['error'] ?? ($resp['data']['error']['message'] ?? 'Error desconocido en BC');
        }

        DB::table('bc_lineas_enviadas')->insert([
            'factura_id' => $f->id,
            'clave' => $clave,
            'importe' => $importe,
            'bc_system_id' => $resp['data']['systemId'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return null;
    }

    /** Código de auditoría: tipo del método de pago, o MULMEDPAG si hubo varios */
    private function metodoPago(FacturaBonoR $f): string
    {
        $tipos = $f->payments->map(fn($p) => $p->method?->tipo)->filter()->unique()->values();

        return match (true) {
            $tipos->isEmpty() => '',
            $tipos->count() > 1 => 'MULMEDPAG',
            default => $tipos->first(),
        };
    }

    private function registrar(FacturaBonoR $f, array $errores, string $facturaCode, string $fecha, string $tercero, int $tarjetas, float $valorTotal): array
    {
        $errores = array_values(array_unique(array_filter($errores)));

        $f->bc_intentos = (int) $f->bc_intentos + 1;
        $f->estado_bc = $errores ? 'ERROR' : 'ENVIADO';
        $f->bc_ultimo_error = $errores ? mb_strimwidth(implode(' | ', $errores), 0, 1000, '…') : null;
        $f->save();

        return [
            'factura_id' => $f->id,
            'numero_factura' => $facturaCode,
            'fecha' => $fecha,
            'cliente_cedula' => $tercero,
            'tarjetas_count' => $tarjetas,
            'valor_total' => $valorTotal,
            'estado_bc' => $f->estado_bc,
            'errores' => $errores,
        ];
    }
}
