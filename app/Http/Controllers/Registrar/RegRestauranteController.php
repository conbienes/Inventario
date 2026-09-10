<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Estado;
use App\Models\Admin\Restaurante;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\restauranteRequest;
use Illuminate\Support\Facades\DB;

class RegRestauranteController extends Controller
{


    public function index()
    {
        $Estado=Estado::all();
        return view('regRestaurante.index',compact('Estado'));
    }


    public function guardar(restauranteRequest $request)
    {
            $save= new Restaurante();
            $save->nit = $request['nit'];
            $save->nombre = $request['nombre'];
            $save->id_estado =$request['id_estado'];

            $save->save();

          return redirect('/home')->with('succes', 'El restaurante ha sido registrado.');
    }


    public function consultar()
    {
        $Restaurante = DB::table('restaurantes')
        ->join('estados',  'estados.id','=','restaurantes.id_estado')
        ->select('restaurantes.id','restaurantes.nit','restaurantes.nombre as Nombre','estados.nombre as Estado')
        ->paginate(8);
        return view('regRestaurante.consultar',compact('Restaurante'));
    }

    public function editar($id)
    {
        $restaurante=Restaurante::findOrFail($id);
        $Estado=Estado::all();
        return view('regRestaurante.editar',compact('restaurante','Estado'));
    }

    public function actualizar(restauranteRequest $request, $id)
    {
        $restaurante=Restaurante::findOrFail($id);
        $restaurante->nit= $request['nit'];
        $restaurante->nombre= $request['nombre'];
        $restaurante->id_estado=$request['id_estado'];
        $restaurante->save();
        return redirect('/home')->with('succes', 'El restaurante ha sido actualizado.');
    }

}
