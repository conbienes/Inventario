<?php

namespace App\Http\Controllers\Inventario\Producto;

use Illuminate\Http\Request;
use App\Models\Inventario\Producto;
use App\Models\Inventario\UnidadMedida;
use App\Models\Inventario\Categoria;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ProductoController extends Controller
{
    public function consultar(Request $request)
    {
        $buscar = strtoupper(trim($request->get('texto', '')));

        // Consulta base
        $query = DB::table('Producto')->join('unidad_medida', 'unidad_medida.Id', '=', 'Producto.IdUnidadMedida')->select('Producto.Id', 'Producto.Nombre', 'Producto.Codigo as Codigo', 'unidad_medida.Nombre as Medida', 'Producto.Estado');

        // Agregar búsqueda dinámica si existe término de búsqueda
        if (!empty($buscar)) {
            $query->where(function ($subquery) use ($buscar) {
                $subquery->where('Producto.Nombre', 'like', "%$buscar%")->orWhere('Producto.Codigo', 'like', "%$buscar%");
            });
        }
        // Paginación final
        $Producto = $query->paginate(20)->appends(['texto' => $buscar]);

        // Retornar la vista
        return view('Inventario.Producto.ConsultarProducto', compact('Producto'));
    }



    public function editar($id)
    {
        $Producto = Producto::findOrFail($id);
        $UnidadMedida = UnidadMedida::all();
        return view('Inventario.Producto.EditarProducto', compact('Producto', 'UnidadMedida'));
    }




    public function Actualizar(Request $request)
    {
        $validated = $request->validate([
            'Nombre' => 'required|min:2|max:100',
            'UnidadMedida' => 'required',
            'Estado' => 'required',
        ]);

        $ActualizarProducto = DB::table('Producto')
            ->where('id', $request['Id'])
            ->update(['Nombre' => strtoupper(trim($request['Nombre'])), 'IdUnidadMedida' => $request['UnidadMedida'], 'Codigo' => trim($request['Codigo']), 'Estado' => $request['Estado']]);

        $Producto = DB::table('Producto')->join('unidad_medida', 'unidad_medida.Id', '=', 'Producto.IdUnidadMedida')->select('Producto.Id', 'Producto.Nombre', 'Producto.Codigo as Codigo', 'unidad_medida.Nombre as Medida', 'Producto.Estado')->paginate(6);

        return view('Inventario.Producto.ConsultarProducto', compact('Producto'));
    }



    public function Crear()
    {

        $ultimoId = Producto::max('id');
        $nuevoId = $ultimoId ? $ultimoId + 1 : 1;
        $UnidadMedida = UnidadMedida::all();
        return view('Inventario.Producto.CrearProducto', compact('UnidadMedida','nuevoId'));
    }

    public function Guardar(Request $request)
    {
        $validated = $request->validate([
            'Nombre' => 'required|min:1|max:100|unique:Producto',
            'Estado' => 'required',
            'UnidadMedida' => 'required',
        ]);

        $save = new Producto();
        $save->Nombre = strtoupper(trim($request['Nombre']));
        $save->Codigo = $request['Codigo'];
        $save->Estado = $request['Estado'];
        $save->IdUnidadMedida = $request['UnidadMedida'];
        $save->IdEmpleado = auth()->user()->id;
        $save->save();

        $Producto = DB::table('Producto')->join('unidad_medida', 'unidad_medida.Id', '=', 'Producto.IdUnidadMedida')->select('Producto.Id', 'Producto.Nombre', 'Producto.Codigo as Codigo', 'unidad_medida.Nombre as Medida', 'Producto.Estado')->paginate(6);

        return view('Inventario.Producto.ConsultarProducto', compact('Producto'));
    }
}
