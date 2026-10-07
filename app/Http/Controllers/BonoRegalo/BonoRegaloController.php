<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use App\Models\BonoRegalo\CargaBC;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\BonoRegalo\Payment;
use App\Models\BonoRegalo\PaymentMethod;
use Illuminate\Support\Facades\DB;
use App\Models\BonoRegalo\FacturaBonoR;
use App\Models\BonoRegalo\TarjetaBonoR;

class BonoRegaloController extends Controller
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
        return view('BonoRegalo.Inicio.index');
    }

    // Vista minimal para imprimir (formato térmico)
    public function printFactura($id)
    {
        // 1) Ubica un registro de CargaBC por id (de tu id_ref) y valida que sea del usuario actual
        $registro = CargaBC::where('id', $id)->firstOrFail();

        // Solo el empleado que hizo la venta o un administrador del módulo puede imprimirla
        abort_unless(
            (int) $registro->idEmpleado === (int) Auth::id() || $this->esAdminBonoRegalo(),
            403,
            'No tienes permiso para imprimir esta factura.'
        );

        // 2) Con ese registro resuelves el número de factura
        $facturaNo = $registro->factura;

        // 3) Carga TODAS las líneas de esa factura (del mismo usuario)
        $lineas = CargaBC::where('factura', $facturaNo)
            ->orderBy('created_at', 'asc')
            ->get(['tarjeta', 'fecha', 'cedula', 'valor_tarjeta']);

        // 4) Totales y campos “representativos”
        $subtotal = (float) $lineas->sum('valor_tarjeta');
        $fechaMin = optional($lineas->min('fecha')) ? \Carbon\Carbon::parse($lineas->min('fecha')) : null;
        $cedula = optional($lineas->first())->cedula;

        // 5) Renderiza la vista térmica basada en CargaBC
        return view('bonoregalo.print.thermal_invoice', [
            'facturaNo' => $facturaNo,
            'lineas' => $lineas,
            'subtotal' => $subtotal,
            'total' => $subtotal, // si no hay descuentos/impuestos, es igual
            'fechaMin' => $fechaMin,
            'cedula' => $cedula,
        ]);
    }

    // Permisos 1 (Administrador) y 2 (Administrador2) en el módulo Bono Regalo (3)
    private function esAdminBonoRegalo(): bool
    {
        return DB::table('empleado_modulo_permiso')
            ->where('id_empleado', Auth::id())
            ->where('id_modulo', 3)
            ->whereIn('id_permiso', [1, 2])
            ->exists();
    }

    public function Reportes(Request $request)
    {
        // Rango por defecto: últimos 30 días
        $from = $request->input('from', Carbon::now('America/Bogota')->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', Carbon::now('America/Bogota')->format('Y-m-d'));

        // Normalizamos límites (inclusive)
        $fromDateTime = Carbon::parse($from, 'America/Bogota')->startOfDay();
        $toDateTime = Carbon::parse($to, 'America/Bogota')->endOfDay();

        // Estados de pago válidos para el dashboard
        $statusOk = ['approved', 'paid', 'success', 'completed'];

        $kpis = FacturaBonoR::query()
        ->whereBetween('fecha', [$fromDateTime, $toDateTime]) // si 'fecha' es DATE/DATETIME
        ->selectRaw('COALESCE(SUM(total),0) as total_vendido, COUNT(*) as transacciones')
        ->first();

        $totalVendido   = (float) ($kpis->total_vendido ?? 0);
        $transacciones  = (int)   ($kpis->transacciones ?? 0);
        $ticketPromedio = $transacciones > 0 ? round($totalVendido / $transacciones, 2) : 0;


        // Distribución por método
        $porMetodo = Payment::query()
            ->join('payment_methods as pm', 'pm.id', '=', 'payments.method_id')
            ->whereBetween('payments.created_at', [$fromDateTime, $toDateTime])
            ->whereIn('payments.status', $statusOk)
            ->groupBy('pm.name')
            ->orderByDesc(DB::raw('SUM(payments.amount)'))
            ->get([DB::raw('pm.name as metodo'), DB::raw('SUM(payments.amount) as total'), DB::raw('COUNT(*) as n')]);

        // Tendencia diaria (línea)
        $tendencia = Payment::query()
            ->whereBetween('payments.created_at', [$fromDateTime, $toDateTime])
            ->whereIn('payments.status', $statusOk)
            ->groupBy(DB::raw('DATE(payments.created_at)'))
            ->orderBy(DB::raw('DATE(payments.created_at)'))
            ->get([DB::raw('DATE(payments.created_at) as fecha'), DB::raw('SUM(amount) as total')]);

        // Ventas por hora (0..23)
        $porHora = Payment::query()
            ->whereBetween('payments.created_at', [$fromDateTime, $toDateTime])
            ->whereIn('payments.status', $statusOk)
            ->groupBy(DB::raw('HOUR(payments.created_at)'))
            ->orderBy(DB::raw('HOUR(payments.created_at)'))
            ->get([DB::raw('HOUR(payments.created_at) as hora'), DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as n')]);

        // Completar 24 horas
        $arrHoraTotal = array_fill(0, 24, 0);
        foreach ($porHora as $r) {
            $arrHoraTotal[(int) $r->hora] = (float) $r->total;
        }

        // Ventas por día (Dom=1..Sáb=7) -> mostraremos Lun..Dom
        $porDia = Payment::query()
            ->whereBetween('payments.created_at', [$fromDateTime, $toDateTime])
            ->whereIn('payments.status', $statusOk)
            ->groupBy(DB::raw('DAYOFWEEK(payments.created_at)'))
            ->orderBy(DB::raw('DAYOFWEEK(payments.created_at)'))
            ->get([DB::raw('DAYOFWEEK(payments.created_at) as dow'), DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as n')]);

        $labelsBarDia = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $totPorDia = array_fill_keys($labelsBarDia, 0);
        $mapDOW = [1 => 'Dom', 2 => 'Lun', 3 => 'Mar', 4 => 'Mié', 5 => 'Jue', 6 => 'Vie', 7 => 'Sáb'];
        foreach ($porDia as $r) {
            $label = $mapDOW[(int) $r->dow] ?? 'N/A';
            if (isset($totPorDia[$label])) {
                $totPorDia[$label] += (float) $r->total;
            } elseif ($label === 'Dom') {
                $totPorDia['Dom'] += (float) $r->total;
            }
        }
        $dataBarDia = array_values($totPorDia);

        // Arrays para Chart.js
        $labelsLinea = $tendencia->pluck('fecha')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'));
        $dataLinea = $tendencia->pluck('total');

        $labelsDonut = $porMetodo->pluck('metodo');
        $dataDonut = $porMetodo->pluck('total');

        $labelsBarHora = range(0, 23);
        $dataBarHora = $arrHoraTotal;

        // Hora pico y día pico
        $horaPico = array_keys($arrHoraTotal, max($arrHoraTotal))[0] ?? null;
        $diaPicoIndex = array_keys($dataBarDia, max($dataBarDia))[0] ?? 0;
        $diaPico = $labelsBarDia[$diaPicoIndex] ?? null;

        // Últimas ventas
        $ultimas = Payment::query()
            ->with(['method', 'invoice'])
            ->whereBetween('payments.created_at', [$fromDateTime, $toDateTime])
            ->whereIn('payments.status', $statusOk)
            ->orderByDesc('payments.created_at')
            ->limit(10)
            ->get();

        /**
         * ================================
         * TARJETAS POR ESTADO (tu SQL)
         * ================================
         * SELECT count(numero) AS CantidadTarjetas, valor, sum(valor)
         * FROM dbinventario.tarjetasbonor
         * WHERE estado='inactiva' GROUP BY valor;
         *
         * SELECT count(numero) AS CantidadTarjetas, valor, sum(valor)
         * FROM dbinventario.tarjetasbonor
         * WHERE estado='activa' GROUP BY valor;
         */
        $tarjetasInactivas = DB::table('item_facturaBonoR as i')
        ->join('facturasBonoR as f', 'i.factura_id', '=', 'f.id')
        ->join('dbinventario.tarjetasbonor as t', 'i.tarjeta_id', '=', 't.id')
        ->where('t.estado', 'inactiva')
        ->whereBetween('f.fecha', [$fromDateTime, $toDateTime])
        ->groupBy('t.valor')
        ->orderBy('t.valor')
        ->select('t.valor')
        ->selectRaw('COUNT(t.numero) as cantidad_tarjetas')
        ->selectRaw('SUM(t.valor) as total_valor')
        ->get();


        $tarjetasActivas = DB::table('dbinventario.tarjetasbonor')
            ->where('estado', 'activa')
            ->groupBy('valor')
            ->orderBy('valor')
            ->get([DB::raw('valor'), DB::raw('COUNT(numero) as cantidad_tarjetas'), DB::raw('SUM(valor) as total_valor')]);

        // Totales por estado (para el pie de cada tarjetica)
        $totActCant = $tarjetasActivas->sum('cantidad_tarjetas');
        $totActVal = $tarjetasActivas->sum('total_valor');
        $totInaCant = $tarjetasInactivas->sum('cantidad_tarjetas');
        $totInaVal = $tarjetasInactivas->sum('total_valor');

        return view('BonoRegalo.Inicio.reporte-ventas', [
            'from' => $fromDateTime->format('Y-m-d'),
            'to' => $toDateTime->format('Y-m-d'),

            'totalVendido' => $totalVendido,
            'transacciones' => $transacciones,
            'ticketPromedio' => $ticketPromedio,

            'porMetodo' => $porMetodo,
            'ultimas' => $ultimas,

            'labelsLinea' => $labelsLinea,
            'dataLinea' => $dataLinea,

            'labelsDonut' => $labelsDonut,
            'dataDonut' => $dataDonut,

            'labelsBarHora' => $labelsBarHora,
            'dataBarHora' => $dataBarHora,

            'labelsBarDia' => $labelsBarDia,
            'dataBarDia' => $dataBarDia,

            'horaPico' => $horaPico,
            'diaPico' => $diaPico,

            // NUEVO: para las tarjetas por estado
            'tarjetasActivas' => $tarjetasActivas,
            'tarjetasInactivas' => $tarjetasInactivas,
            'totActCant' => $totActCant,
            'totActVal' => $totActVal,
            'totInaCant' => $totInaCant,
            'totInaVal' => $totInaVal,
        ]);
    }

    public function resumen(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $from = $request->query('from');
        $to = $request->query('to');

        $fromDT = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->startOfDay();
        $toDT = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();

        $statusOk = ['approved', 'paid', 'success', 'completed'];

        // Base: pagos del rango y con estado OK, cuyas facturas pertenecen al usuario logueado
        $base = Payment::query()
            ->whereBetween('created_at', [$fromDT, $toDT])
            ->whereIn('status', $statusOk)
            ->whereHas('invoice', function ($q) use ($userId) {
                $q->where('usuario_id', $userId);
            });

        // Totales generales
        $row = (clone $base)->selectRaw('COALESCE(SUM(amount),0) as total, COUNT(*) as n')->first();

        // Detalle por método de pago (incluye method_id NULL -> "N/D")
        $porMetodo = (clone $base)
            ->select(['method_id', DB::raw('COALESCE(SUM(amount),0) as total'), DB::raw('COUNT(*) as n')])
            ->groupBy('method_id')
            ->with(['method:id,name']) // para traer el nombre del método si existe
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->method_id, // puede ser null
                    'name' => $p->method->name ?? 'N/D', // fallback si no hay relación
                    'total' => (float) $p->total,
                    'n' => (int) $p->n,
                ];
            })
            ->sortByDesc('total')
            ->values();

        return response()->json([
            'user_id' => $userId,
            'from' => $fromDT->toDateString(),
            'to' => $toDT->toDateString(),
            'total' => (float) ($row->total ?? 0),
            'transacciones' => (int) ($row->n ?? 0),
            'currency' => 'COP',
            'methods' => $porMetodo,
        ]);
    }

    public function resumenFactura(Request $request)
    {
        $W = (int) $request->query('w', 76); // ancho mm (para CSS @page)
        $from = $request->query('from');
        $to = $request->query('to');

        $fromDT = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->startOfDay();
        $toDT = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();
        if ($fromDT->gt($toDT)) {
            [$fromDT, $toDT] = [$toDT->copy()->startOfDay(), $fromDT->copy()->endOfDay()];
        }

        $user = auth()->user();
        abort_unless($user, 401);

        $statusOk = ['approved', 'paid', 'success', 'completed'];

        // Facturas del usuario + items + pagos (con método)
        $facturas = FacturaBonoR::query()
            ->with([
                'items:id,factura_id,tarjeta_id',
                'payments' => function ($q) use ($statusOk) {
                    $q->whereIn('status', $statusOk)->with(['method:id,name']);
                },
            ])
            ->where('usuario_id', $user->id)
            ->whereBetween('fecha', [$fromDT->toDateString(), $toDT->toDateString()])
            ->orderBy('fecha')
            ->get();

        // Prefetch tarjetas
        $tarjetaIds = $facturas->flatMap(fn($f) => $f->items->pluck('tarjeta_id'))->filter()->unique()->values();
        $tarjetasMap = TarjetaBonoR::whereIn('id', $tarjetaIds)
            ->get(['id', 'numero', 'valor', 'estado'])
            ->keyBy('id');

        // Listado de TARJETAS (una línea por tarjeta: numero + valor)
        $tarjetas = [];
        $tarjetasCount = 0;
        $tarjetasTotal = 0.0;

        foreach ($facturas as $f) {
            foreach ($f->items as $it) {
                $t = $tarjetasMap->get($it->tarjeta_id);
                if ($t) {
                    $tarjetas[] = [
                        'numero' => $t->numero,
                        'valor' => (float) $t->valor,
                    ];
                    $tarjetasCount++;
                    $tarjetasTotal += (float) $t->valor;
                }
            }
        }

        // MEDIOS DE PAGO (nombre -> total y cantidad)
        $paymentsAll = $facturas->flatMap->payments;
        $metodos = $paymentsAll
            ->groupBy(fn($p) => $p->method->name ?? 'N/D')
            ->map(
                fn($grp) => [
                    'name' => $grp->first()->method->name ?? 'N/D',
                    'n' => (int) $grp->count(),
                    'total' => (float) $grp->sum('amount'),
                ],
            )
            ->sortByDesc('total')
            ->values();

        $totalRecibido = (float) $paymentsAll->sum('amount');

        // Datos cabecera (ajusta a tu realidad o saca de .env)
        $empresa = [
            'nombre' => 'ADMINISTRACION MAYORCA S.A.S',
            'dir' => 'CL 51 SUR 48 57 ET1 P8',
            'tel' => '6042333',
        ];

        return view('BonoRegalo.print.print_resumen_termico', [
            'Wmm' => $W,
            'empresa' => $empresa,
            'userName' => $user->nombre ?? ($user->name ?? $user->email),
            'fromDT' => $fromDT,
            'toDT' => $toDT,
            'tarjetas' => $tarjetas,
            'tarjetasCount' => $tarjetasCount,
            'tarjetasTotal' => $tarjetasTotal,
            'metodos' => $metodos,
            'totalRecibido' => $totalRecibido,
        ]);
    }
}
