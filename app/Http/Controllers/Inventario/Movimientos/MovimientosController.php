<?php

namespace App\Http\Controllers\Inventario\Movimientos;

use Illuminate\Http\Request;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Movimientos;
use App\Models\Inventario\Inventario;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;


class MovimientosController extends Controller
{
    public function editar($id)
    {
        if (Auth::check())
        {
            $id =  Crypt::decrypt($id);
            $Inventario=Inventario::findOrFail($id);

            $Inventario = DB::table('Inventario')
            ->join('Producto as p','p.Id','=','Inventario.IdProducto')
            ->select('Inventario.Id','p.Nombre as Producto','Inventario.Cantidades')
            ->where('Inventario.Id','=',$id )->get();

            return view('Inventario.Movimientos.EditarMovimientos',compact('Inventario'));
        }
        else
        {
        return redirect('/');
        }
    }

    //public function Actualizar(Request $request)
    //{
    //    if (Auth::check())
    //    {
//
    //        DD($request);
    //        $validated = $request->validate([
    //            'Nombre' => 'required|min:2|max:100',
    //            'UnidadMedida' => 'required',
    //            'Estado' => 'required',
    //        ]);
//
    //
    //        $ActualizarProducto= DB::table('Producto')
    //        ->where('id', $request['Id'])
    //        ->update(['Nombre' => strtoupper(trim($request['Nombre'])),
    //                'IdUnidadMedida'=>  $request['UnidadMedida'],
    //                'Codigo'=>  trim($request['Codigo']),
    //                'Estado'=>$request['Estado']]);
//
    //        $Movimientos = DB::table('Movimientos')
    //        ->join('unidad_medida', 'unidad_medida.Id', '=', 'Producto.IdUnidadMedida')
    //        ->select('Producto.Id','Producto.Nombre','Producto.Codigo as Codigo','unidad_medida.Nombre as Medida','Producto.Estado')->paginate(6);
    //
    //
    //        return view('Inventario.Movimientos.ConsultarMovimientos',compact('Movimientos'));
    //    }
    //    else
    //    {
    //    return redirect('/');
    //    }
    //
    //}

  //  public function Crear()
  //  {
  //      if (Auth::check())
  //      {
  //          $Movimientos=Movimientos::all();
  //          return view('Inventario.Movimientos.CrearMovimientos',compact('Movimientos'));
  //      }
  //      else
  //      {
  //      return redirect('/');
  //      }
  //  }


    public function Guardar(Request $request)
    {
        if (Auth::check())
        {

            if ($request['Tipo']==1) {

                $save= new Movimientos();
                $save->Cantidad= $request['Cantidad'];
                $save->IdTipoMovimiento= 7;
                $save->Descripcion=strtoupper(trim( $request['Descripcion']));
                $save->IdEmpleado=auth()->user()->id;
                $save->IdArea=$request['Area'];
                $save->IdSolicitante= $request['Empleado'];
                $save->IdInventario= $request['Id'];
                $save->save();


                $Num= DB::table('Inventario')->select("Cantidades")->where('id', $request['Id'])->get();


                foreach ($Num as $item)
                {
                    $Cantidad= $item->Cantidades - $request['Cantidad'];
                }

                $Inv= DB::table('Inventario')
                ->where('id', $request['Id'])
                ->update(['Cantidades' => $Cantidad]);

                Session::flash('success','La salida del producto con el código '.$request['Id'].' fue correcta ');

                return Redirect::to('/Inventario');
            }
            else
            {
                $save= new Movimientos();
                $save->Cantidad= $request['Cantidad'];
                $save->IdTipoMovimiento= 8;
                $save->Descripcion=strtoupper(trim( $request['Descripcion']));
                $save->IdEmpleado=auth()->user()->id;
                $save->IdArea=$request['Area'];
                $save->IdSolicitante= $request['Empleado'];
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
            }

            Session::flash('success','El ingreso del producto con el codigo '.$request['Id'].' fue correcta ');

            return Redirect::to('/Inventario');
        }
        else
        {
        return redirect('/');
        }

    }

}
