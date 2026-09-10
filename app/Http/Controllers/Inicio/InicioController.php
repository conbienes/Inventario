<?php

namespace App\Http\Controllers\Inicio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventario\solicitudPedidos;
use App\Models\Inventario\Movimientos;
use App\Models\Inventario\Inventario;
use App\Models\Inventario\TipoMov;
use App\Models\Inventario\Empleado;
use App\Models\Inventario\Area;
use Auth;
use Illuminate\Support\Facades\DB;

class InicioController extends Controller
{

      public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Verificar que el módulo seleccionado sea Bono Regalo (2)
            if (session('modulo_seleccionado') != 1) {
                abort(403, 'Acceso no autorizado para este módulo');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::check())
        {
            if (auth()->user()->id_tipoempleado==1)
            {
                $solicitudPedidos=solicitudPedidos::where('IdSolicita',auth()->user()->id)->where('Estado','=','1')->get();
                $Aprobadas=solicitudPedidos::where('IdSolicita',auth()->user()->id)->where('Estado','=','2')->get();
                $pendientes=solicitudPedidos::where('Estado','=','1')->get();
                $TotalAprobadas=solicitudPedidos::where('Estado','=','2')->get();
                $Inventario=DB::table('Inventario')->whereRaw('Inventario.CantidadMin > Inventario.Cantidades')->get();

                return view('Inicio.inicioInventario',compact('solicitudPedidos','Aprobadas','pendientes','TotalAprobadas','Inventario'));
            }
            else
            {
                $solicitudPedidos=solicitudPedidos::where('IdSolicita',auth()->user()->id)->where('Estado','=','1')->get();
                $Aprobadas=solicitudPedidos::where('IdSolicita',auth()->user()->id)->where('Estado','=','2')->get();
                $Inventario=DB::table('Inventario')->whereRaw('Inventario.CantidadMin > Inventario.Cantidades')->get();


                return view('Inicio.inicioInventario',compact('solicitudPedidos','Aprobadas'));
            }
        }
        else
        {
          return redirect('/');
        }
    }

}
