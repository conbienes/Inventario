<?php

namespace App\Http\Controllers\Inventario\Categoria;

use Illuminate\Http\Request;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Categoria;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CategoriaController extends Controller
{
    public function consultar(Request $request)
    {
        $buscar=strtoupper(trim($request->get('texto')));

        $Categoria = DB::table('categoria_inventario')
        ->where("Nombre",'like',"%".$buscar."%")->paginate(10);

        return view('Inventario.Categoria.ConsultarCategoria',compact('Categoria'));
    }

    public function editar($id)
    {
        $Categoria=Categoria::findOrFail($id);
        return view('Inventario.Categoria.EditarCategoria',compact('Categoria'));
    }

    public function Actualizar(Request $request)
    {
        $validated = $request->validate([
            'Nombre' => 'required|min:2|max:100',
            'Estado' => 'required',
        ]);


        $ActualizarAlmacen= DB::table('categoria_inventario')
        ->where('id', $request['Id'])
        ->update(['Nombre' => strtoupper(trim($request['Nombre'])),
                'Estado'=>$request['Estado']]);

                $Categoria = DB::table('categoria_inventario')->paginate(10);

        return view('Inventario.Categoria.ConsultarCategoria',compact('Categoria'));

    }

    public function Crear()
    {
      return view('Inventario.Categoria.CrearCategoria');
    }

    public function Guardar(Request $request)
    {

       /* $validated = $request->validate([
            'Nombre' => 'required|min:2|max:100|unique:Categoria',
            'Estado' => 'required',
        ]);*/

        $save= new Categoria();
        $save->Nombre=strtoupper(trim( $request['Nombre']));
        $save->Estado=$request['Estado'];
        $save->IdEmpleado=auth()->user()->id;
        $save->save();

        $Categoria = DB::table('categoria_inventario')->paginate(10);

        return view('Inventario.Categoria.ConsultarCategoria',compact('Categoria'));
    }
}
