<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use App\Http\Requests\BonoRegalo\FiltrosRequest;
use App\Jobs\BonoRegalo\EnviarFacturaBCJob;
use App\Jobs\BonoRegalo\SincronizarClienteBCJob;
use App\Models\BonoRegalo\ClienteBonoR;
use App\Models\BonoRegalo\FacturaBonoR;
use App\Services\BonoRegalo\ClienteBCSyncService;
use App\Services\BonoRegalo\FacturaBCService;
use Illuminate\Http\Request;

/** Contabilidad de Bono Regalo: listado por estado en BC y envío de facturas/clientes a Business Central. */
class ReporteVentasController extends Controller
{
    public function Contabilidad(FiltrosRequest $request)
    {
        $allEstados = ['ENVIADO', 'ERROR', 'PENDIENTE'];

        $estados = array_values(array_intersect((array) $request->input('estado', $allEstados), $allEstados)) ?: $allEstados;
        $desde = $request->input('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->input('hasta', now()->toDateString());
        $q = trim((string) $request->input('q', ''));

        $facturas = FacturaBonoR::with(['cliente:id,nombre,apellidos,razons,cedula', 'usuario:id,nombre'])
            ->withSum(['payments as pagado' => fn($p) => $p->exitosos()], 'amount')
            ->whereIn('estado_bc', $estados)
            // fecha es DATE: comparación directa (usa el índice estado_bc+fecha)
            ->when($desde, fn($qq) => $qq->where('fecha', '>=', $desde))
            ->when($hasta, fn($qq) => $qq->where('fecha', '<=', $hasta))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('numero_factura', 'like', "%{$q}%")
                        ->orWhereHas('cliente', fn($c) => $c->where('nombre', 'like', "%{$q}%")
                            ->orWhere('apellidos', 'like', "%{$q}%")
                            ->orWhere('razons', 'like', "%{$q}%")
                            ->orWhere('cedula', 'like', "%{$q}%"));
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
