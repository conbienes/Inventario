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
                return route('Inicio');
            case 3: // Bono Regalo
                return route('BonoRegalo.inicio');
            default:
                return route('Inicio');
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
