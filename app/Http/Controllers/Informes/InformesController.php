<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\Alertastock;
use App\Exports\UsersExport;
use App\Exports\ArqueoExport;
use App\Exports\ExportarSolicitudes;
use App\Exports\EntradasAlmacen;
use Maatwebsite\Excel\Facades\Excel;
use Auth;

class InformesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     private function resolveModuleHome(int $modulo): string
    {
        switch ($modulo) {
            case 1: // Inventario
                return route('Inicio');
            case 2: // Restaurante
                return route('BonoRegalo.Inicio.index');
            case 3: // Bono Regalo
                return route('BonoRegalo.inicio');
            default:
                return route('home');
        }
    }

    public function index()
    {
       if (Auth::check()) {
            // Si ya está logueado, redirige según módulo guardado
            return redirect($this->resolveModuleHome(Auth::user()->modulo_actual));
        }

        return view('Seguridad.login');
    }

    public function buscar(Request $request)
    {
        $ini = strtotime($request->get('FechaI'));
        $ini = date('Y-m-d H:i:s', $ini);
        $fin = strtotime($request->get('FechaF') . '23:59:00');
        $fin = date('Y-m-d H:i:s', $fin);

        $info = DB::table('regtickets')
            ->join('empleados', 'empleados.id', '=', 'regtickets.id_empleado')
            ->join('restaurantes', 'restaurantes.id', '=', 'regtickets.id_restaurante')
            ->join('estados', 'estados.id', '=', 'regtickets.id_estado')

            ->join('divisiones', 'divisiones.id', '=', 'empleados.id_div')
            ->select('regtickets.id', 'empleados.cedula', 'empleados.nombre as Nombres', 'empleados.apellidos', 'divisiones.nombre as div', 'restaurantes.nombre as Restaurante', 'estados.nombre as Estado', 'regtickets.created_at')
            ->where('regtickets.created_at', '>=', date($ini))
            ->where('regtickets.created_at', '<=', date($fin))
            ->paginate(3000);

        $ini = $request->get('FechaI');
        $fin = $request->get('FechaF');

        return view('Informes.informes', compact('info', 'ini', 'fin'));
    }

    public function export(Request $request)
    {
        return Excel::download(new UsersExport($request), 'Info.xlsx');
    }

    public function ExportarArqueo(Request $request)
    {
        $Division = DB::table('divisiones')
            ->where('Id', '=', "$request->IdEmpresa")
            ->get();

        foreach ($Division as $item) {
            $nombre = $item->nombre;
        }

        return Excel::download(new ArqueoExport($request), 'ARQUEO ' . $nombre . '.xlsx');
    }

    public function ExportarAlertas(Request $request)
    {
        $Division = DB::table('divisiones')
            ->where('Id', '=', "$request->IdEmpresa")
            ->get();

        foreach ($Division as $item) {
            $nombre = $item->nombre;
        }

        return Excel::download(new Alertastock($request), 'ALERTASTOCK ' . $nombre . '.xlsx');
    }

    public function Solicitudes()
    {
        return Excel::download(new ExportarSolicitudes(), 'Solicitudes.xlsx');
    }

    public function EntradasAlmacen()
    {
        return Excel::download(new EntradasAlmacen(), 'Entradas Almacen.xlsx');
    }
}
