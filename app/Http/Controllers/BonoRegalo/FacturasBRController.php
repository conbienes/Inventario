<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use App\Actions\BonoRegalo\RegistrarVentaBono;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\BonoRegalo\TarjetaBonoR;
use App\Models\BonoRegalo\ClienteBonoR;
use App\Models\BonoRegalo\CargaBC;
use App\Models\BonoRegalo\PaymentMethod;
use Illuminate\Support\Facades\Auth;
use App\Exports\FacturasBRMultiExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

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

    public function store(Request $request, RegistrarVentaBono $registrarVenta)
    {
        $request->validate(
            [
                'fecha' => 'required|date',
                'cliente' => 'required|exists:clientesBonoR,id',
                'tarjetas' => 'required|array|min:1',
                'tarjetas.*' => 'integer|distinct|exists:tarjetasBonoR,id',
                // 'precios' se ignora: el precio siempre es el valor de la tarjeta en BD
                'precios' => 'nullable|array',
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
                'payments.required' => 'Debe registrar al menos un medio de pago.',
            ],
        );

        try {
            $factura = $registrarVenta->handle(
                ClienteBonoR::findOrFail($request->cliente),
                array_map('intval', $request->tarjetas),
                $request->payments,
                (int) Auth::id(),
            );

            return redirect()
                ->route('BonoRegalo.IndexFacturas')
                ->with('success', "Recibo De Caja # {$factura->numero_factura} creado correctamente.");
        } catch (\DomainException $e) {
            // Reglas de negocio: el mensaje es para el usuario
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'No se pudo registrar la venta. Intenta de nuevo o contacta a soporte.')->withInput();
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
            report($e);

            return back()
                ->withErrors(['error' => 'Ocurrió un error al buscar las facturas. Revisa los filtros e intenta de nuevo.'])
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
