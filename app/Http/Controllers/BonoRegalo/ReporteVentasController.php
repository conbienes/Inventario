<?php
// app/Http/Controllers/BonoRegalo/ReporteVentasController.php
namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BonoRegalo\Payment;
use App\Models\BonoRegalo\FacturaBonoR;
use App\Models\BonoRegalo\ClienteBonoR;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Jobs\BonoRegalo\EnviarFacturaBCJob;
use App\Jobs\BonoRegalo\SincronizarClienteBCJob;
use App\Services\BonoRegalo\ClienteBCSyncService;
use App\Services\BonoRegalo\FacturaBCService;

class ReporteVentasController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $fromDT = Carbon::parse($from)->startOfDay();
        $toDT = Carbon::parse($to)->endOfDay();
        $statusOk = ['approved', 'paid', 'success', 'completed'];

        $kpis = Payment::whereBetween('created_at', [$fromDT, $toDT])
            ->whereIn('status', $statusOk)
            ->selectRaw('COALESCE(SUM(amount),0) as total_vendido, COUNT(*) as transacciones')
            ->first();

        $totalVendido = (float) $kpis->total_vendido;
        $transacciones = (int) $kpis->transacciones;
        $ticketPromedio = $transacciones ? round($totalVendido / $transacciones, 2) : 0;

        $porMetodo = Payment::query()
            ->join('payment_methods as pm', 'pm.id', '=', 'payments.method_id')
            ->whereBetween('payments.created_at', [$fromDT, $toDT])
            ->whereIn('payments.status', $statusOk)
            ->groupBy('pm.name')
            ->orderByDesc(DB::raw('SUM(payments.amount)'))
            ->get([DB::raw('pm.name as metodo'), DB::raw('SUM(payments.amount) as total'), DB::raw('COUNT(*) as n')]);

        $tendencia = Payment::whereBetween('created_at', [$fromDT, $toDT])
            ->whereIn('status', $statusOk)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get([DB::raw('DATE(created_at) as fecha'), DB::raw('SUM(amount) as total')]);

        $ultimas = Payment::with(['method', 'invoice'])
            ->whereBetween('created_at', [$fromDT, $toDT])
            ->whereIn('status', $statusOk)
            ->latest()
            ->limit(10)
            ->get();

        return view('bono-regalo.reporte-ventas', [
            'from' => $fromDT->format('Y-m-d'),
            'to' => $toDT->format('Y-m-d'),
            'totalVendido' => $totalVendido,
            'transacciones' => $transacciones,
            'ticketPromedio' => $ticketPromedio,
            'porMetodo' => $porMetodo,
            'ultimas' => $ultimas,
            'labelsLinea' => $tendencia->pluck('fecha')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')),
            'dataLinea' => $tendencia->pluck('total'),
            'labelsDonut' => $porMetodo->pluck('metodo'),
            'dataDonut' => $porMetodo->pluck('total'),
        ]);
    }

    // ==== EXPORT A EXCEL ====
    public function exportExcel(Request $request)
    {
        // composer require maatwebsite/excel:^3.1
        // php artisan make:export VentasExport --model=Payment
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        return \Excel::download(new \App\Exports\VentasExport($from, $to), "ventas_{$from}_{$to}.xlsx");
    }

    // ==== EXPORT A PDF ====
    public function exportPdf(Request $request)
    {
        // composer require barryvdh/laravel-dompdf:^2.0
        // php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $statusOk = ['approved', 'paid', 'success', 'completed'];
        $data = Payment::with('method', 'invoice')
            ->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
            ->whereIn('status', $statusOk)
            ->get();

        $pdf = \PDF::loadView('bono-regalo.exports.ventas-pdf', [
            'from' => $from,
            'to' => $to,
            'rows' => $data,
        ])->setPaper([0, 0, 612, 792], 'portrait'); // carta

        return $pdf->download("ventas_{$from}_{$to}.pdf");
    }

    public function Contabilidad(Request $request)
    {
        // Estados permitidos
        $allEstados = ['ENVIADO', 'ERROR', 'PENDIENTE'];

        // Filtros llegados por GET
        $estados = (array) $request->input('estado', $allEstados);
        $estados = array_values(array_intersect($estados, $allEstados));
        if (empty($estados)) {
            $estados = $allEstados;
        }

        $desde = $request->input('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->input('hasta', now()->toDateString());
        $q = trim((string) $request->input('q', ''));

        $facturas = FacturaBonoR::with(['cliente:id,nombre', 'usuario:id,nombre'])
            ->withSum(
                [
                    'payments as pagado' => function ($q2) {
                        $q2->whereIn('status', ['approved', 'paid', 'success', 'completed']);
                    },
                ],
                'amount',
            )
            ->whereIn('estado_bc', $estados)
            ->when($desde, fn($qq) => $qq->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($qq) => $qq->whereDate('fecha', '<=', $hasta))
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('numero_factura', 'like', "%{$q}%")->orWhereHas('cliente', fn($c) => $c->where('nombre', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('fecha')
            ->paginate(200)
            ->appends($request->query());

        return view('BonoRegalo.Contabilidad.Index', compact('facturas', 'estados', 'allEstados', 'desde', 'hasta', 'q'));
    }

    public function CargarBC(Request $req, FacturaBCService $facturaBC)
    {
        $ids = collect((array) $req->input('ids', []))->filter(fn($v) => is_numeric($v))->map(fn($v) => (int) $v)->unique()->values();
        if ($ids->isEmpty()) {
            return response()->json(['message' => 'Debe enviar ids[] de facturas.'], 422);
        }

        if (!config('services.bc.company_id')) {
            return response()->json(['error' => 'Falta configurar BC_COMPANY_ID'], 500);
        }

        $facturas = FacturaBonoR::with(FacturaBCService::RELACIONES)->whereIn('id', $ids)->orderByDesc('fecha')->get();
        $notFound = $ids->diff($facturas->pluck('id'))->values()->all();

        // Las facturas ya enviadas no se reenvían (evita asientos duplicados en BC)
        [$yaEnviadas, $pendientes] = $facturas->partition(fn($f) => $f->estado_bc === 'ENVIADO');
        $skipped = $yaEnviadas->pluck('id')->values()->all();

        // En cola (requiere worker): responde de inmediato
        if (config('services.bc.async')) {
            $pendientes->each(fn($f) => EnviarFacturaBCJob::dispatch($f->id));

            return response()->json([
                'mode' => 'encolado',
                'requested_ids' => $ids->all(),
                'missing_ids' => $notFound,
                'skipped_ids' => $skipped,
                'queued' => $pendientes->count(),
                'processed' => 0,
                'results' => [],
            ]);
        }

        // En la misma petición
        $results = [];
        foreach ($pendientes as $f) {
            $r = $facturaBC->enviar($f);
            if (!empty($r['omitida'])) {
                $skipped[] = $f->id; // otra petición la estaba enviando o ya la envió
                continue;
            }
            $results[] = $r;
        }

        return response()->json([
            'mode' => 'cargar',
            'requested_ids' => $ids->all(),
            'missing_ids' => $notFound,
            'skipped_ids' => $skipped, // ya estaban ENVIADO o en proceso
            'processed' => count($results),
            'results' => $results,
        ]);
    }

    public function CargarClientes(Request $req, ClienteBCSyncService $sync)
    {
        $limit = max(1, min((int) $req->input('limit', 200), 1000));

        // Clientes pendientes de cargar (cargado = 0 o NULL)
        $clientes = ClienteBonoR::query()
            ->where(fn($q) => $q->whereNull('cargado')->orWhere('cargado', 0))
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if (config('services.bc.async')) {
            $clientes->each(fn($c) => SincronizarClienteBCJob::dispatch($c->id));

            return response()->json(['mode' => 'encolado', 'queued' => $clientes->count(), 'processed' => 0, 'ok' => 0, 'errors' => 0]);
        }

        $res = $clientes->map(fn($c) => $sync->sincronizar($c));

        return response()->json([
            'mode' => 'cargar_clientes',
            'processed' => $res->count(),
            'ok' => $res->where('ok', true)->count(), // cliente y dimensión OK
            'errors' => $res->where('ok', false)->count(),
            'customers_ok' => $res->where('ok_customer', true)->count(),
            'customers_error' => $res->where('ok_customer', false)->count(),
            'dimensions_ok' => $res->where('ok_dimension', true)->count(),
            'dimensions_error' => $res->where('ok_dimension', false)->count(),
        ]);
    }
}
