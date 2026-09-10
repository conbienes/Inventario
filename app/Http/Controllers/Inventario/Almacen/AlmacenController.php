<?php

namespace App\Http\Controllers\Inventario\Almacen;

use Illuminate\Http\Request;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Division;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;


class AlmacenController extends Controller
{
    public function consultar(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {

        $buscar=strtoupper(trim($request->get('texto')));

        $Almacen = DB::table('Almacen')
            ->where("Nombre",'like',"%".$buscar."%")->paginate(10);

        return view('Inventario.Almacen.ConsultarAlmacen',compact('Almacen'));
        }
        else
        {
          return redirect('/');
        }
    }




    public function editar($id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            $Almacen=Almacen::findOrFail($id);
            return view('Inventario.Almacen.EditarAlmacen',compact('Almacen'));
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
                    'Nombre' => 'required|min:2|max:100'
                ]);

                $ActualizarAlmacen= DB::table('Almacen')
                ->where('id', $request['Id'])
                ->update(['Nombre' => strtoupper(trim($request['Nombre'])),
                        'Estado'=>$request['Estado']]);

                $Almacen = DB::table('Almacen')->paginate(10);

                return view('Inventario.Almacen.ConsultarAlmacen',compact('Almacen'));
        }
        else
        {
            return redirect('/');
        }

    }

    public function Crear()
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
             return view('Inventario.Almacen.CrearAlmacen');
        }
        else
        {
            return redirect('/');
        }
    }

    public function Guardar(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            $validated = $request->validate([
                'Nombre' => 'required|unique:Almacen|min:2|max:100'
            ]);

            $save= new Almacen();
            $save->Nombre=strtoupper(trim( $request['Nombre']));
            $save->Estado=$request['Estado'];
            $save->IdEmpleado=auth()->user()->id;
            $save->save();

            $Almacen = DB::table('Almacen')->paginate(10);

            return view('Inventario.Almacen.ConsultarAlmacen',compact('Almacen'));
        }
        else
        {
            return redirect('/');
        }
    }
}
