<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\BonoRegalo\TarjetaBonoR;
use App\Models\BonoRegalo\ClienteBonoR;
use App\Models\BonoRegalo\ItemFacturaBonoR;
use App\Models\BonoRegalo\CargaBC;
use App\Models\BonoRegalo\PaymentMethod;
use App\Models\BonoRegalo\Payment;
use App\Models\BonoRegalo\FacturaBonoR;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Exports\FacturasBRMultiExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use App\Mail\FacturaBonoMail;

class FacturasBRController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Verificar que el módulo seleccionado sea Bono Regalo (2)
            if (session('modulo_seleccionado') != 3) {
                abort(403, 'Acceso no autorizado para este módulo');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $tarjetas = TarjetaBonoR::all();
        $paymentMethods = PaymentMethod::all();

        return view('BonoRegalo.Facturas.index', compact('tarjetas', 'paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $request->validate(
            [
                'fecha' => 'required|date',
                'cliente' => 'required|exists:clientesBonoR,id',
                'tarjetas' => 'required|array|min:1',
                'tarjetas.*' => 'integer|exists:tarjetasBonoR,id',
                'precios' => 'required|array|min:1',
                'precios.*' => 'numeric|min:0',
                'payments' => 'required|array|min:1',
                'payments.*.method_id' => 'required|integer|exists:payment_methods,id',
                'payments.*.amount' => 'required|numeric|min:0.01',
                'payments.*.reference' => 'nullable|string|max:100',
            ],
            [
                'fecha.required' => 'La fecha es obligatoria.',
                'cliente.required' => 'Debe seleccionar un cliente.',
                'cliente.exists' => 'El cliente seleccionado no existe.',
                'tarjetas.required' => 'Debe seleccionar al menos una tarjeta.',
                'precios.required' => 'Debe ingresar al menos un precio.',
                'payments.required' => 'Debe registrar al menos un medio de pago.',
            ],
        );

        if (count($request->tarjetas) !== count($request->precios)) {
            return back()->with('error', 'La cantidad de tarjetas no coincide con la de precios.');
        }

        $serie = 'BR';
        $cliente = ClienteBonoR::findOrFail($request->cliente);

        try {
            return DB::transaction(function () use ($request, $serie, $cliente) {
                // 1) Secuencia atómica
                DB::statement('UPDATE secuencias SET valor = LAST_INSERT_ID(valor + 1) WHERE nombre = ?', ['BR']);
                $next = (int) DB::selectOne('SELECT LAST_INSERT_ID() AS seq')->seq;

                // 2) Formato con ceros a la izquierda (4 dígitos -> BR0001, BR0002, ...)
                $width = 7; // cambia a 5 si quisieras BR00001
                $numeroFactura = $serie . str_pad($next, $width, '0', STR_PAD_LEFT);

                // 3) Crear factura
                $factura = FacturaBonoR::create([
                    'numero_factura' => $numeroFactura,
                    'fecha' => now()->setTimezone('America/Bogota'),
                    'cliente_id' => $cliente->id,
                    'usuario_id' => Auth::id(),
                    'total' => 0,
                    'estado_bc' => 'PENDIENTE',
                ]);


                $mapNumero = TarjetaBonoR::whereIn('id', $request->tarjetas)->pluck('numero', 'id'); // [ id => numero ]

                $tarjetasFallidas = [];
                $valorExitoso = 0.0;
                $itemsCorreo = [];
                $valorTotal = array_sum($request->precios);

                // 3) Procesar tarjetas
                foreach ($request->tarjetas as $i => $tarjetaId) {
                    // Consumo atómico
                    $actualizadas = TarjetaBonoR::where('id', $tarjetaId)
                        ->where('estado', 'activa')
                        ->lockForUpdate()
                        ->update(['estado' => 'inactiva']);

                    if ($actualizadas !== 1) {
                        $tarjetasFallidas[] = $mapNumero[$tarjetaId] ?? "ID {$tarjetaId}";

                        continue;
                    }

                    // Ya consumida

                    $tarjeta = TarjetaBonoR::find($tarjetaId);
                    $precio = (float) ($request->precios[$i] ?? 0);

                    ItemFacturaBonoR::create([
                        'factura_id' => $factura->id,
                        'tarjeta_id' => $tarjetaId,
                        'cantidad' => 1,
                        'precio_unitario' => $precio,
                    ]);

                    $a = $this->cargarTablaBC($cliente->id, $numeroFactura, $tarjetaId, $precio, $valorTotal);

                    $valorExitoso += $precio;
                    $itemsCorreo[] = [
                        'numero_tarjeta' => $tarjeta->numero, // número para el PDF/correo
                        'precio' => $precio,
                    ];
                }

                // 4) Total real
                $factura->update(['total' => $valorExitoso]);

                $totalPagos = collect($request->payments)->sum(function ($p) {
                    return (float) ($p['amount'] ?? 0);
                });

                // Exigir que cubra el total exitoso (o permite parcial si quieres)
                if (round($totalPagos, 2) < round($valorExitoso, 2)) {
                    throw new \Exception('El total de pagos no cubre el total de la factura.');
                }

                // Guardar pagos
                foreach ($request->payments as $p) {
                    Payment::create([
                        'invoice_id' => $factura->id,
                        'method_id' => (int) $p['method_id'],
                        'amount' => (float) $p['amount'],
                        'reference' => $p['reference'] ?? null,
                        'status' => 'paid', // o 'pending' si lo confirmarás por webhook
                    ]);
                }

                // (Opcional) calcular cambio si sobrepaga
                $cambio = max(0, round($totalPagos - $valorExitoso, 2));

                $outbox = \App\Models\MailOutbox::create([
                    'mailable' => \App\Mail\FacturaBonoMail::class,
                    'cliente_id' => $cliente->id ?? null,
                    'factura_id' => $factura->id,
                    'to' => $cliente->correo,
                    'subject' => 'Recibo Bono Regalo ' . $factura->numero_factura,
                    'status' => 'queued',
                    'payload' => ['items' => $itemsCorreo], // datos mínimos para reintento
                    'queued_at' => now(),
                ]);

                // 5) Correo (en cola, after-commit)
                if (!empty($cliente->correo) && $valorExitoso > 0) {
                    Mail::to($cliente->correo)->queue((new FacturaBonoMail($cliente, $factura, $itemsCorreo, $outbox->id))->afterCommit());
                }

                // 6) Respuesta
                if (!empty($tarjetasFallidas)) {
                    return redirect()
                        ->route('BonoRegalo.IndexFacturas')
                        ->with('warning', [
                            'mensaje' => 'Algunas tarjetas no se pudieron agregar.',
                            'tarjetas' => $tarjetasFallidas, // ← ya son NÚMEROS
                            'totalExitoso' => $valorExitoso,
                            'factura' => $factura->numero_factura,
                        ]);
                }

                return redirect()
                    ->route('BonoRegalo.IndexFacturas')
                    ->with('success', "Recibo De Caja # {$factura->numero_factura} creado correctamente.");
            }, 5);
        } catch (\Throwable $e) {

            return back()->with('error', 'Error: ' . $e->getMessage());

        }
    }

    public function cargarTablaBC($usuario, $facturaId, $tarjetaId, $precio, $valorTotal)
    {
        try {
            $cliente = ClienteBonoR::find($usuario);

            if (!$cliente) {
                throw new \RuntimeException("Cliente {$usuario} no encontrado.");
            }

            $tarjeta = TarjetaBonoR::find($tarjetaId);

            if (!$tarjeta) {
                throw new \RuntimeException("Tarjeta {$tarjetaId} no encontrada.");
            }

            // Normalizadores útiles
            $toUpperOrNull = function ($v) {
                $v = is_string($v) ? trim($v) : $v;
                return $v === '' || $v === null ? null : mb_strtoupper($v, 'UTF-8');
            };

            $payload = [
                'nombre' => $toUpperOrNull($cliente->nombre),
                'segundo_nombre' => $toUpperOrNull($cliente->segundo_nombre),
                'apellidos' => $toUpperOrNull($cliente->apellidos),
                'segundo_apellido' => $toUpperOrNull($cliente->segundo_apellido),

                'tipo_documento' => $cliente->tipo_documento, // si lo guardas como código (CC, CE, etc.)
                'tipoActividad' => $cliente->tipoActividad, // CLI-NRI / CLI-JUR

                'cedula' => $cliente->cedula,
                'correo' => mb_strtolower($cliente->correo ?? '', 'UTF-8'),

                'factura' => $facturaId,
                'tarjeta' => $tarjeta->numero,
                'fecha' => now()->setTimezone('America/Bogota'),

                'cargadoBC' => 0,
                'valor_tarjeta' => (float) $precio,
                'valor_total' => (float) $valorTotal,

                'idEmpleado' => auth()->id(),
            ];

            // Crea el registro
            $registro = CargaBC::create($payload);

            if (!$registro) {
                throw new \RuntimeException('Error al guardar en CargaBC');
            }

            return $registro;
        } catch (\Throwable $e) {
            Log::error('Error en cargarTablaBC: ' . $e->getMessage());
            throw $e; // Re-lanzar para manejo superior
        }
    }

    /**
     * Display the specified resource.
     */
    public function informesTarjetas(Request $request)
    {
        try {
            // Prefiltro: MIS ventas
            $base = CargaBC::query();

            // Si no hay filtros, mostrar HOY
            if (!$request->filled('fecha_inicio') && !$request->filled('fecha_fin') && !$request->filled('numero') && !$request->filled('cedula')) {
                $base->whereDate('fecha', now('America/Bogota')->toDateString());
            }

            // Filtros
            if ($request->filled('numero')) {
                $base->where('factura', 'like', '%' . $request->numero . '%');
            }
            if ($request->filled('cedula')) {
                $base->where('cedula', 'like', '%' . $request->cedula . '%');
            }
            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $fi = Carbon::parse($request->fecha_inicio)->startOfDay();
                $ff = Carbon::parse($request->fecha_fin)->endOfDay();
                $base->whereBetween('fecha', [$fi, $ff]);
            } elseif ($request->filled('fecha_inicio')) {
                $fi = Carbon::parse($request->fecha_inicio)->startOfDay();
                $base->where('fecha', '>=', $fi);
            } elseif ($request->filled('fecha_fin')) {
                $ff = Carbon::parse($request->fecha_fin)->endOfDay();
                $base->where('fecha', '<=', $ff);
            }

            // Map de columnas ordenables (usa agregados)
            $sort = $request->get('sort', 'fecha');
            $direction = $request->get('direction', 'desc');

            $sortMap = [
                'factura' => 'factura',
                'tarjeta' => 'tarjetas', // ordena por el string concatenado
                'fecha' => 'fecha_min', // tomamos la primera fecha de la factura
                'cedula' => 'cedula_min',
                'valor_tarjeta' => 'valor_total', // suma por factura
                'valor_total' => 'valor_total',
            ];
            $sortColumn = $sortMap[$sort] ?? 'fecha_min';
            $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'desc';

            // AGRUPADO por factura:
            // - tarjetas: GROUP_CONCAT
            // - fecha_min: MIN(fecha) (para mostrar/ordenar con una fecha representativa)
            // - cedula_min: MIN(cedula) (evita ONLY_FULL_GROUP_BY)
            // - valor_total: SUM(valor_tarjeta)
            $facturas = $base
                ->selectRaw(
                    "
                factura,
                GROUP_CONCAT(tarjeta ORDER BY tarjeta SEPARATOR ', ') AS tarjetas,
                MIN(fecha)  AS fecha_min,
                MIN(cedula) AS cedula_min,
                SUM(valor_tarjeta) AS valor_total,
                MIN(id) AS id_ref -- id de referencia para imprimir
            ",
                )
                ->groupBy('factura')
                ->orderBy($sortColumn, $direction)
                ->paginate(10)
                ->appends($request->all());

            return view('BonoRegalo.Facturas.BuscarFacturas', compact('facturas'));
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['error' => 'Ocurrió un error al buscar las facturas: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function exportarExcel(Request $request)
    {
        // Lee parámetros de la query (GET)
        $data = $request->query();

        // Valida
        $validated = Validator::make($data, [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ])->validate();

        // Defaults
        $from = $validated['from'] ?? now('America/Bogota')->toDateString();
        $to = $validated['to'] ?? $from;

        // Request "limpio" para pasar al export
        $exportRequest = new Request([
            'from' => $from,
            'to' => $to,
        ]);

        return Excel::download(new FacturasBRMultiExport($exportRequest), 'facturas_br.xlsx');
    }

    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
