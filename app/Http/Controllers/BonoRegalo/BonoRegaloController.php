<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use App\Http\Requests\BonoRegalo\FiltrosRequest;
use App\Models\BonoRegalo\CargaBC;
use App\Models\BonoRegalo\FacturaBonoR;
use App\Models\BonoRegalo\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class BonoRegaloController extends Controller
{
    public function index()
    {
        return view('BonoRegalo.Inicio.index');
    }

    // Vista minimal para imprimir (formato térmico). $id es un id de CargaBC (id_ref del listado)
    public function printFactura($id)
    {
        $registro = CargaBC::findOrFail($id);

        // Solo el empleado que hizo la venta o un administrador del módulo puede imprimirla
        abort_unless(Gate::allows('bono-regalo.ver-venta', (int) $registro->idEmpleado), 403, 'No tienes permiso para imprimir esta factura.');

        $lineas = CargaBC::where('factura', $registro->factura)
            ->orderBy('created_at')
            ->get(['tarjeta', 'fecha', 'cedula', 'valor_tarjeta']);

        $subtotal = (float) $lineas->sum('valor_tarjeta');

        return view('BonoRegalo.print.thermal_invoice', [
            'facturaNo' => $registro->factura,
            'lineas' => $lineas,
            'subtotal' => $subtotal,
            'total' => $subtotal, // sin descuentos ni impuestos
            'fechaMin' => $lineas->min('fecha') ? Carbon::parse($lineas->min('fecha')) : null,
            'cedula' => $lineas->first()?->cedula,
        ]);
    }

    /**
     * Dashboard de ventas. Todas las cifras de venta salen de las facturas (lo vendido);
     * solo la distribución por método sale de los pagos.
     */
    public function Reportes(FiltrosRequest $request)
    {
        $desde = Carbon::parse($request->input('from', now()->subDays(30)->toDateString()))->startOfDay();
        $hasta = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        $facturas = FacturaBonoR::whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()]);

        // KPIs
        $kpis = (clone $facturas)->selectRaw('COALESCE(SUM(total),0) as total_vendido, COUNT(*) as transacciones')->first();
        $totalVendido = (float) $kpis->total_vendido;
        $transacciones = (int) $kpis->transacciones;
        $ticketPromedio = $transacciones > 0 ? round($totalVendido / $transacciones, 2) : 0;

        // Distribución por método de pago
        $porMetodo = Payment::exitosos()
            ->join('payment_methods as pm', 'pm.id', '=', 'payments.method_id')
            ->whereBetween('payments.created_at', [$desde, $hasta])
            ->groupBy('pm.name')
            ->orderByDesc(DB::raw('SUM(payments.amount)'))
            ->get([DB::raw('pm.name as metodo'), DB::raw('SUM(payments.amount) as total'), DB::raw('COUNT(*) as n')]);

        // Tendencia diaria
        $tendencia = (clone $facturas)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get([DB::raw('fecha'), DB::raw('SUM(total) as total')]);

        // Ventas por hora (0..23), según la hora de registro de la factura
        $arrHoraTotal = array_fill(0, 24, 0);
        (clone $facturas)
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->get([DB::raw('HOUR(created_at) as hora'), DB::raw('SUM(total) as total')])
            ->each(function ($r) use (&$arrHoraTotal) {
                $arrHoraTotal[(int) $r->hora] = (float) $r->total;
            });

        // Ventas por día de la semana (MySQL: 1=Dom … 7=Sáb), se muestran Lun..Dom
        $labelsBarDia = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $mapDOW = [1 => 'Dom', 2 => 'Lun', 3 => 'Mar', 4 => 'Mié', 5 => 'Jue', 6 => 'Vie', 7 => 'Sáb'];
        $totPorDia = array_fill_keys($labelsBarDia, 0);
        (clone $facturas)
            ->groupBy(DB::raw('DAYOFWEEK(fecha)'))
            ->get([DB::raw('DAYOFWEEK(fecha) as dow'), DB::raw('SUM(total) as total')])
            ->each(function ($r) use (&$totPorDia, $mapDOW) {
                $totPorDia[$mapDOW[(int) $r->dow]] += (float) $r->total;
            });
        $dataBarDia = array_values($totPorDia);

        // Últimas ventas (pagos)
        $ultimas = Payment::exitosos()
            ->with(['method', 'invoice'])
            ->whereBetween('payments.created_at', [$desde, $hasta])
            ->orderByDesc('payments.created_at')
            ->limit(10)
            ->get();

        // Tarjetas vendidas en el rango, agrupadas por valor
        $tarjetasInactivas = DB::table('item_facturaBonoR as i')
            ->join('facturasBonoR as f', 'i.factura_id', '=', 'f.id')
            ->join('tarjetasBonoR as t', 'i.tarjeta_id', '=', 't.id')
            ->where('t.estado', 'inactiva')
            ->whereBetween('f.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('t.valor')
            ->orderBy('t.valor')
            ->select('t.valor')
            ->selectRaw('COUNT(t.numero) as cantidad_tarjetas')
            ->selectRaw('SUM(t.valor) as total_valor')
            ->get();

        // Inventario disponible hoy (no depende del rango de fechas)
        $tarjetasActivas = DB::table('tarjetasBonoR')
            ->where('estado', 'activa')
            ->groupBy('valor')
            ->orderBy('valor')
            ->get([DB::raw('valor'), DB::raw('COUNT(numero) as cantidad_tarjetas'), DB::raw('SUM(valor) as total_valor')]);

        $horaPico = array_keys($arrHoraTotal, max($arrHoraTotal))[0] ?? null;
        $diaPico = $labelsBarDia[array_keys($dataBarDia, max($dataBarDia))[0] ?? 0] ?? null;

        return view('BonoRegalo.Inicio.reporte-ventas', [
            'from' => $desde->format('Y-m-d'),
            'to' => $hasta->format('Y-m-d'),

            'totalVendido' => $totalVendido,
            'transacciones' => $transacciones,
            'ticketPromedio' => $ticketPromedio,

            'porMetodo' => $porMetodo,
            'ultimas' => $ultimas,

            'labelsLinea' => $tendencia->pluck('fecha')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')),
            'dataLinea' => $tendencia->pluck('total'),

            'labelsDonut' => $porMetodo->pluck('metodo'),
            'dataDonut' => $porMetodo->pluck('total'),

            'labelsBarHora' => range(0, 23),
            'dataBarHora' => $arrHoraTotal,

            'labelsBarDia' => $labelsBarDia,
            'dataBarDia' => $dataBarDia,

            'horaPico' => $horaPico,
            'diaPico' => $diaPico,

            'tarjetasActivas' => $tarjetasActivas,
            'tarjetasInactivas' => $tarjetasInactivas,
            'totActCant' => $tarjetasActivas->sum('cantidad_tarjetas'),
            'totActVal' => $tarjetasActivas->sum('total_valor'),
            'totInaCant' => $tarjetasInactivas->sum('cantidad_tarjetas'),
            'totInaVal' => $tarjetasInactivas->sum('total_valor'),
        ]);
    }

    /** JSON: lo recaudado por el usuario logueado en el rango, por método de pago */
    public function resumen(FiltrosRequest $request)
    {
        $userId = (int) auth()->id();
        [$desde, $hasta] = $this->rango($request);

        $base = Payment::exitosos()
            ->whereBetween('created_at', [$desde, $hasta])
            ->whereHas('invoice', fn($q) => $q->where('usuario_id', $userId));

        $row = (clone $base)->selectRaw('COALESCE(SUM(amount),0) as total, COUNT(*) as n')->first();

        // Detalle por método de pago (method_id NULL -> "N/D")
        $porMetodo = (clone $base)
            ->select(['method_id', DB::raw('COALESCE(SUM(amount),0) as total'), DB::raw('COUNT(*) as n')])
            ->groupBy('method_id')
            ->with(['method:id,name'])
            ->get()
            ->map(fn($p) => [
                'id' => $p->method_id,
                'name' => $p->method->name ?? 'N/D',
                'total' => (float) $p->total,
                'n' => (int) $p->n,
            ])
            ->sortByDesc('total')
            ->values();

        return response()->json([
            'user_id' => $userId,
            'from' => $desde->toDateString(),
            'to' => $hasta->toDateString(),
            'total' => (float) ($row->total ?? 0),
            'transacciones' => (int) ($row->n ?? 0),
            'currency' => 'COP',
            'methods' => $porMetodo,
        ]);
    }

    /** Cierre de caja del usuario logueado en impresión térmica */
    public function resumenFactura(FiltrosRequest $request)
    {
        $user = auth()->user();
        [$desde, $hasta] = $this->rango($request);

        $facturas = FacturaBonoR::query()
            ->with([
                'items:id,factura_id,tarjeta_id',
                'items.tarjeta:id,numero,valor',
                'payments' => fn($q) => $q->exitosos()->with('method:id,name'),
            ])
            ->where('usuario_id', $user->id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->orderBy('fecha')
            ->get();

        // Una línea por tarjeta vendida
        $tarjetas = $facturas->flatMap->items
            ->filter(fn($it) => $it->tarjeta)
            ->map(fn($it) => ['numero' => $it->tarjeta->numero, 'valor' => (float) $it->tarjeta->valor])
            ->values();

        // Medios de pago (nombre -> total y cantidad)
        $pagos = $facturas->flatMap->payments;
        $metodos = $pagos
            ->groupBy(fn($p) => $p->method->name ?? 'N/D')
            ->map(fn($grp, $nombre) => ['name' => $nombre, 'n' => $grp->count(), 'total' => (float) $grp->sum('amount')])
            ->sortByDesc('total')
            ->values();

        $empresa = config('bono_regalo.empresa');

        return view('BonoRegalo.print.print_resumen_termico', [
            'Wmm' => (int) $request->input('w', 76), // ancho mm (CSS @page), validado 48..120
            'empresa' => ['nombre' => $empresa['nombre'], 'dir' => $empresa['direccion'], 'tel' => $empresa['telefono']],
            'userName' => $user->nombre ?? ($user->name ?? $user->email),
            'fromDT' => $desde,
            'toDT' => $hasta,
            'tarjetas' => $tarjetas->all(),
            'tarjetasCount' => $tarjetas->count(),
            'tarjetasTotal' => (float) $tarjetas->sum('valor'),
            'metodos' => $metodos,
            'totalRecibido' => (float) $pagos->sum('amount'),
        ]);
    }

    /** Rango from/to (por defecto hoy), con los límites del día incluidos */
    private function rango(FiltrosRequest $request): array
    {
        return [
            Carbon::parse($request->input('from', now()->toDateString()))->startOfDay(),
            Carbon::parse($request->input('to', now()->toDateString()))->endOfDay(),
        ];
    }
}
