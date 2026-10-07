<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Actions\BonoRegalo\RegistrarVentaBono;
use App\Exports\FacturasBRMultiExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\BonoRegalo\FiltrosRequest;
use App\Http\Requests\BonoRegalo\VentaBonoRequest;
use App\Models\BonoRegalo\CargaBC;
use App\Models\BonoRegalo\ClienteBonoR;
use App\Models\BonoRegalo\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class FacturasBRController extends Controller
{
    public function index()
    {
        // Las tarjetas se buscan por AJAX (buscarTarjetasBR); aquí solo los métodos de pago
        $paymentMethods = PaymentMethod::all();

        return view('BonoRegalo.Facturas.index', compact('paymentMethods'));
    }

    public function store(VentaBonoRequest $request, RegistrarVentaBono $registrarVenta)
    {
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

    /** Buscar facturas (agrupadas por número). El cajero ve sus ventas; el administrador, todas. */
    public function informesTarjetas(FiltrosRequest $request)
    {
        $base = CargaBC::query()
            ->unless(Gate::allows('bono-regalo.admin'), fn($q) => $q->where('idEmpleado', Auth::id()));

        $hayFiltros = collect($request->only(['fecha_inicio', 'fecha_fin', 'numero', 'cedula']))->filter()->isNotEmpty();

        // Sin filtros: ventas de hoy
        $base->when(!$hayFiltros, fn($q) => $q->where('fecha', now()->toDateString()))
            ->when($request->filled('numero'), fn($q) => $q->where('factura', 'like', '%' . $request->numero . '%'))
            ->when($request->filled('cedula'), fn($q) => $q->where('cedula', 'like', '%' . $request->cedula . '%'))
            ->when($request->filled('fecha_inicio'), fn($q) => $q->where('fecha', '>=', Carbon::parse($request->fecha_inicio)->toDateString()))
            ->when($request->filled('fecha_fin'), fn($q) => $q->where('fecha', '<=', Carbon::parse($request->fecha_fin)->toDateString()));

        // Columnas ordenables (sobre los agregados)
        $sortMap = [
            'factura' => 'factura',
            'tarjeta' => 'tarjetas',
            'fecha' => 'fecha_min',
            'cedula' => 'cedula_min',
            'valor_tarjeta' => 'valor_total',
            'valor_total' => 'valor_total',
        ];
        $sortColumn = $sortMap[$request->get('sort', 'fecha')] ?? 'fecha_min';
        $direction = strtolower($request->get('direction', 'desc'));

        // Agrupado por factura (MIN/SUM evitan problemas con ONLY_FULL_GROUP_BY)
        $facturas = $base
            ->selectRaw("
                factura,
                GROUP_CONCAT(tarjeta ORDER BY tarjeta SEPARATOR ', ') AS tarjetas,
                MIN(fecha) AS fecha_min,
                MIN(cedula) AS cedula_min,
                SUM(valor_tarjeta) AS valor_total,
                MIN(id) AS id_ref
            ")
            ->groupBy('factura')
            ->orderBy($sortColumn, $direction)
            ->paginate(10)
            ->appends($request->query());

        return view('BonoRegalo.Facturas.BuscarFacturas', compact('facturas'));
    }

    public function exportarExcel(FiltrosRequest $request)
    {
        $from = $request->input('from', now()->toDateString());
        $to = $request->input('to', $from);

        return Excel::download(new FacturasBRMultiExport(new Request(['from' => $from, 'to' => $to])), 'facturas_br.xlsx');
    }
}
