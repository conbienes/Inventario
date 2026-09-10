<?php

namespace App\Http\Controllers\Inventario\Alertastock;

use Illuminate\Http\Request;
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
            $buscar=strtoupper(trim($request->get('texto')));
            $Division = Division::findOrFail($id);

            if (!empty($buscar) )
            {
                    $Inventario = DB::table('Inventario')
                    ->join('Producto as p','p.Id','=','Inventario.IdProducto')
                    ->join('Divisiones as d','d.Id','=','Inventario.IdDivisiones')
                    ->join('Almacen as a','a.Id','=','Inventario.IdAlmacen')
                    ->join('categoria_inventario as c','c.Id','=','Inventario.IdCategoria')
                    ->select('Inventario.Id','Inventario.Cantidades','Inventario.CantidadMin','Inventario.CantidadMax',
                    'p.Nombre as Producto','d.Nombre as Division','a.Nombre as Almacen','c.Nombre as Categoria','p.Codigo')
                    ->where('d.Id','=',$id)
                    ->where('p.Nombre','like','%'.$buscar.'%')
                    ->whereRaw('Inventario.CantidadMin > Inventario.Cantidades')
                    ->OrderBy('Inventario.Cantidades' ,'desc')->paginate(10);
            }
            else
            {
                $Inventario = DB::table('Inventario')
                ->join('Producto as p','p.Id','=','Inventario.IdProducto')
                ->join('Divisiones as d','d.Id','=','Inventario.IdDivisiones')
                ->join('Almacen as a','a.Id','=','Inventario.IdAlmacen')
                ->join('categoria_inventario as c','c.Id','=','Inventario.IdCategoria')
                ->select('Inventario.Id','Inventario.Cantidades','Inventario.CantidadMin','Inventario.CantidadMax',
                'p.Nombre as Producto','d.Nombre as Division','a.Nombre as Almacen','c.Nombre as Categoria','p.Codigo')
                ->where('d.Id','=',$id )
                ->whereRaw('Inventario.CantidadMin > Inventario.Cantidades')
                ->OrderBy('Inventario.Cantidades' ,'desc')->paginate(10);
            }

            return view('Inventario.AlertaStock.AlertaStock',compact('Inventario','Division'));
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

            $Division = DB::table('divisiones')->get();

            return view('Inventario.AlertaStock.Inicio',compact('Division'));
        }
        else
        {
             return redirect('/');
        }
    }

}



