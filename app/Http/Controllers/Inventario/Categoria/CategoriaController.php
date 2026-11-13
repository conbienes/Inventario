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
        $buscar = strtoupper(trim((string) $request->get('texto', '')));

        $query = DB::table('categoria_inventario');
        if ($buscar !== '') {
            $query->where(function ($w) use ($buscar) {
                $w->where('Nombre', 'like', '%' . $buscar . '%');
            });
        }
        if (in_array((string) $request->get('estado'), ['1', '2'], true)) {
            $query->where('Estado', (int) $request->get('estado'));
        }

        match ((string) $request->get('orden', 'nombre')) {
            'nombre_desc' => $query->orderByDesc('Nombre'),
            'recientes' => $query->orderByDesc('created_at')->orderByDesc('Id'),
            default => $query->orderBy('Nombre'),
        };

        $Categoria = $query->paginate(20)->withQueryString();

        return view('Inventario.Categoria.ConsultarCategoria', compact('Categoria'));
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

        return redirect()->route('Categoria')->with('success', 'Categoría actualizada correctamente.');

    }

    public function Crear()
    {
      return view('Inventario.Categoria.CrearCategoria');
    }

    public function Guardar(Request $request)
    {

        // Se normaliza antes de validar para que la comprobación de duplicados compare igual que se guarda (mayúsculas).
        $request->merge(['Nombre' => strtoupper(trim((string) $request->input('Nombre')))]);

        $validated = $request->validate([
            'Nombre' => 'required|max:100|unique:categoria_inventario,Nombre',
        ], [
            'Nombre.required' => 'El nombre de la categoría es obligatorio.',
            'Nombre.max' => 'El nombre de la categoría no puede tener más de 100 caracteres.',
            'Nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);

        $save= new Categoria();
        $save->Nombre=strtoupper(trim( $request['Nombre']));
        $save->Estado=$request['Estado'];
        $save->IdEmpleado=auth()->user()->id;
        $save->save();

        return redirect()->route('Categoria')->with('success', 'Categoría registrada correctamente.');
    }
}
