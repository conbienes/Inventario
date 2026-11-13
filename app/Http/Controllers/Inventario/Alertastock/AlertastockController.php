<?php

namespace App\Http\Controllers\Inventario\Alertastock;

use Illuminate\Http\Request;
use App\Services\Inventario\FiltrosInventario;
use App\Models\Inventario\solicitudPedidos;
use App\Models\Inventario\Movimientos;
use App\Models\Inventario\Inventario;
use App\Models\Inventario\TipoMov;
use App\Models\Inventario\Empleado;
use App\Models\Inventario\Division;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Mail\ProdAprob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
Use Session;
Use Redirect;


class AlertastockController extends Controller
{
    public function AlertaStock($id,Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            $id =  Crypt::decrypt($id);
            $Division = Division::findOrFail($id);
            divisionInventarioActual((int) $id);

            // Solo productos por debajo de su mínimo; se pueden filtrar además por "sin existencia" o "bajo el mínimo"
            $filtros = new FiltrosInventario((int) $id);
            $consulta = $filtros->consulta()->whereRaw('Inventario.CantidadMin > Inventario.Cantidades');
            $filtros->aplicar($consulta, $request, ['sin', 'bajo']);
            $Inventario = $filtros->ordenar($consulta, $request, 'faltante')->paginate(20)->withQueryString();
            $opciones = $filtros->opciones();

            return view('Inventario.AlertaStock.AlertaStock',compact('Inventario','Division','opciones'));
        }
        else
        {
          return redirect('/');
        }
    }

    public function InicioAlertaStock()
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {

            // Va directo a la empresa con la que se está trabajando; se cambia desde el selector de la vista.
            $division = divisionInventarioActual();
            if ($division === null) {
                $Division = collect();
                return view('Inventario.AlertaStock.Inicio', compact('Division'));
            }

            session()->reflash(); // conserva los mensajes de éxito/error de la acción anterior

            return redirect()->route('AlertaStock', ['id' => Crypt::encrypt($division)]);
        }
        else
        {
             return redirect('/');
        }
    }

}



