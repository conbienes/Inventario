<?php

namespace App\Actions\BonoRegalo;

use App\Mail\FacturaBonoMail;
use App\Models\BonoRegalo\CargaBC;
use App\Models\BonoRegalo\ClienteBonoR;
use App\Models\BonoRegalo\FacturaBonoR;
use App\Models\BonoRegalo\ItemFacturaBonoR;
use App\Models\BonoRegalo\Payment;
use App\Models\BonoRegalo\TarjetaBonoR;
use App\Models\MailOutbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Registra una venta de tarjetas Bono Regalo (recibo de caja).
 *
 * Todo o nada: si alguna tarjeta no está disponible o los pagos no cubren el total,
 * se lanza DomainException y no queda nada guardado (ni consecutivo, ni tarjetas consumidas).
 * El precio de cada tarjeta es siempre su valor en BD, nunca el del formulario.
 */
class RegistrarVentaBono
{
    private const SERIE = 'BR';
    private const ANCHO_CONSECUTIVO = 7; // BR0000001

    /**
     * @param  int[]  $tarjetaIds
     * @param  array<int, array{method_id: int, amount: float|string, reference?: ?string}>  $pagos
     *
     * @throws \DomainException  regla de negocio, el mensaje es para el usuario
     */
    public function handle(ClienteBonoR $cliente, array $tarjetaIds, array $pagos, int $usuarioId): FacturaBonoR
    {
        return DB::transaction(function () use ($cliente, $tarjetaIds, $pagos, $usuarioId) {
            // 1) Bloquear las tarjetas pedidas (orden por id para evitar deadlocks entre cajeros)
            $tarjetas = TarjetaBonoR::whereIn('id', $tarjetaIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            $noDisponibles = collect($tarjetaIds)
                ->reject(fn($id) => ($tarjetas[$id]->estado ?? null) === 'activa')
                ->map(fn($id) => $tarjetas[$id]->numero ?? "ID {$id}");

            if ($noDisponibles->isNotEmpty()) {
                throw new \DomainException(
                    'No se registró la venta. Estas tarjetas ya no están disponibles (vendidas o inactivas): '
                    . $noDisponibles->implode(', ') . '. Quítalas e intenta de nuevo.'
                );
            }

            $total = (float) $tarjetas->sum('valor');
            $totalPagos = (float) collect($pagos)->sum(fn($p) => (float) ($p['amount'] ?? 0));

            if (round($totalPagos, 2) < round($total, 2)) {
                throw new \DomainException('El total de pagos no cubre el total de la factura.');
            }

            // 2) Consecutivo atómico (se revierte con la transacción si algo falla)
            DB::statement('UPDATE secuencias SET valor = LAST_INSERT_ID(valor + 1) WHERE nombre = ?', [self::SERIE]);
            $siguiente = (int) DB::selectOne('SELECT LAST_INSERT_ID() AS seq')->seq;
            $numeroFactura = self::SERIE . str_pad($siguiente, self::ANCHO_CONSECUTIVO, '0', STR_PAD_LEFT);

            // 3) Factura, consumo de tarjetas e ítems
            $ahora = now();
            $factura = FacturaBonoR::create([
                'numero_factura' => $numeroFactura,
                'fecha' => $ahora,
                'cliente_id' => $cliente->id,
                'usuario_id' => $usuarioId,
                'total' => $total,
                'estado_bc' => 'PENDIENTE',
            ]);

            TarjetaBonoR::whereIn('id', $tarjetas->keys())->update(['estado' => 'inactiva']);

            $items = [];
            $cargaBC = [];
            $itemsCorreo = [];
            $mayus = fn($v) => ($v = trim((string) $v)) === '' ? null : mb_strtoupper($v, 'UTF-8');

            foreach ($tarjetaIds as $id) {
                $t = $tarjetas[$id];
                $precio = (float) $t->valor;

                $items[] = [
                    'factura_id' => $factura->id,
                    'tarjeta_id' => $t->id,
                    'cantidad' => 1,
                    'precio_unitario' => $precio,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                // Copia desnormalizada que usan los informes e impresión térmica
                $cargaBC[] = [
                    'nombre' => $mayus($cliente->nombre),
                    'segundo_nombre' => $mayus($cliente->segundo_nombre),
                    'apellidos' => $mayus($cliente->apellidos),
                    'segundo_apellido' => $mayus($cliente->segundo_apellido),
                    'tipo_documento' => $cliente->tipo_documento,
                    'tipoActividad' => $cliente->tipoActividad,
                    'cedula' => $cliente->cedula,
                    'correo' => mb_strtolower((string) $cliente->correo, 'UTF-8'),
                    'factura' => $numeroFactura,
                    'tarjeta' => $t->numero,
                    'fecha' => $ahora->toDateString(),
                    'cargadoBC' => 0,
                    'valor_tarjeta' => $precio,
                    'valor_total' => $total,
                    'idEmpleado' => $usuarioId,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];

                $itemsCorreo[] = ['numero_tarjeta' => $t->numero, 'precio' => $precio];
            }

            ItemFacturaBonoR::insert($items);
            CargaBC::insert($cargaBC);

            // 4) Pagos
            foreach ($pagos as $p) {
                Payment::create([
                    'invoice_id' => $factura->id,
                    'method_id' => (int) $p['method_id'],
                    'amount' => (float) $p['amount'],
                    'reference' => $p['reference'] ?? null,
                    'status' => 'paid',
                ]);
            }

            // 5) Recibo por correo (solo si el cliente tiene correo). Se encola al hacer commit.
            if (!empty($cliente->correo)) {
                $outbox = MailOutbox::create([
                    'mailable' => FacturaBonoMail::class,
                    'cliente_id' => $cliente->id,
                    'factura_id' => $factura->id,
                    'to' => $cliente->correo,
                    'subject' => 'Recibo Bono Regalo ' . $numeroFactura,
                    'status' => 'queued',
                    'payload' => ['items' => $itemsCorreo],
                    'queued_at' => $ahora,
                ]);

                Mail::to($cliente->correo)->queue((new FacturaBonoMail($cliente, $factura, $itemsCorreo, $outbox->id))->afterCommit());
            }

            return $factura;
        }, 5);
    }
}
