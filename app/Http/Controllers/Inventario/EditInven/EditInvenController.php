<?php

namespace App\Http\Controllers\Inventario\EditInven;

use Illuminate\Http\Request;
use App\Models\Inventario\Division;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Inventario;
use App\Models\Inventario\Categoria;
use App\Models\Inventario\Movimientos;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class EditInvenController extends Controller
{

    public function editar($id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            $id =  Crypt::decrypt($id);

            $EditInven = DB::table('Inventario')
                ->join('Producto as p','p.Id','=','Inventario.IdProducto')
                ->join('Divisiones as d','d.Id','=','Inventario.IdDivisiones')
                ->join('Almacen as a','a.Id','=','Inventario.IdAlmacen')
                ->join('categoria_inventario as c','c.Id','=','Inventario.IdCategoria')
                ->select('Inventario.Id','Inventario.Cantidades','p.Nombre as Producto','d.Nombre as Division',
                'a.Nombre as Almacen','c.Nombre as Categoria')
                ->where("Inventario.Id",'=',$id)->get();

            return view('Inventario.EditInven.EditarEditInven',compact('EditInven'));
        }
        else
        {
        return redirect('/');
        }

    }

    public function EditarInventario($id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            $id =  Crypt::decrypt($id);

            $EditInven = DB::table('Inventario')
                ->join('Producto as p','p.Id','=','Inventario.IdProducto')
                ->join('Divisiones as d','d.Id','=','Inventario.IdDivisiones')
                ->join('Almacen as a','a.Id','=','Inventario.IdAlmacen')
                ->join('categoria_inventario as c','c.Id','=','Inventario.IdCategoria')
                ->select('Inventario.Id','Inventario.Cantidades','p.Nombre as Producto','d.Nombre as Division',
                'a.Nombre as Almacen','c.Nombre as Categoria')
                ->where("Inventario.Id",'=',$id)->get();

            return view('Inventario.EditInven.EditarInventario',compact('EditInven'));
        }
        else
        {
        return redirect('/');
        }

    }

    public function Actualizar(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
                    $validated = $request->validate([
                    'Descripcion' => 'required|min:0|max:255',
                    'Cantidad' => 'required|numeric',
                ]);

                $save= new Movimientos();
                $save->Cantidad= $request['Cantidad'];
                $save->IdTipoMovimiento= 6;
                $save->Descripcion=strtoupper(trim( $request['Descripcion']));
                $save->IdEmpleado=auth()->user()->id;
                $save->IdArea= auth()->user()->id_area;
                $save->IdSolicitante= auth()->user()->id;
                $save->IdInventario= $request['Id'];
                $save->save();

                $Num= DB::table('Inventario')->select("Cantidades")->where('id', $request['Id'])->get();

                foreach ($Num as $item)
                {
                    $Cantidad= $item->Cantidades + $request['Cantidad'];
                }

                $Inv= DB::table('Inventario')
                ->where('id', $request['Id'])
                ->update(['Cantidades' => $Cantidad]);

                Session::flash('succes','El producto con el código '.$request['Id'].' se actualizó correctamente ');

                return Redirect::to('/Inventario');
            }
            else
            {
            return redirect('/');
            }

    }
    public function ActualizarInventario(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {

                $validated = $request->validate([
                  'Estanteria' => 'required',
                  'Entrepaño' => 'required',
                  'Gaveta' => 'required',
                ]);


                $Inv= DB::table('Inventario')
                ->where('Id', $request['Id'])
                ->update(['Estanteria' => $request['Estanteria'],'Entrepano' => $request['Entrepaño'],'Gaveta' => $request['Gaveta']]);

                Session::flash('succes','Se actualizo correctamente');

                return Redirect::to('/Inventario');
            }
            else
            {
            return redirect('/');
            }

    }
}
