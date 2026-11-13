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
        $buscar = strtoupper(trim((string) $request->get('texto', '')));

        $query = DB::table('Producto')
            ->join('unidad_medida', 'unidad_medida.Id', '=', 'Producto.IdUnidadMedida')
            ->select('Producto.Id', 'Producto.Nombre', 'Producto.Codigo as Codigo', 'unidad_medida.Nombre as Medida', 'Producto.Estado');

        if ($buscar !== '') {
            $query->where(function ($w) use ($buscar) {
                $w->where('Producto.Nombre', 'like', "%$buscar%")->orWhere('Producto.Codigo', 'like', "%$buscar%");
            });
        }
        if ($unidad = (int) $request->get('unidad')) {
            $query->where('Producto.IdUnidadMedida', $unidad);
        }
        if (in_array((string) $request->get('estado'), ['1', '2'], true)) {
            $query->where('Producto.Estado', (int) $request->get('estado'));
        }

        match ((string) $request->get('orden', 'nombre')) {
            'nombre_desc' => $query->orderByDesc('Producto.Nombre'),
            'codigo' => $query->orderByRaw('CAST(Producto.Codigo AS UNSIGNED)')->orderBy('Producto.Codigo'),
            'recientes' => $query->orderByDesc('Producto.created_at')->orderByDesc('Producto.Id'),
            default => $query->orderBy('Producto.Nombre'),
        };

        $Producto = $query->paginate(20)->withQueryString();
        $unidades = DB::table('unidad_medida')->orderBy('Nombre')->pluck('Nombre', 'Id')->all();

        return view('Inventario.Producto.ConsultarProducto', compact('Producto', 'unidades'));
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

        return redirect()->route('Producto')->with('success', 'Producto actualizado correctamente.');
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
        // Se normaliza antes de validar para que la comprobación de duplicados compare igual que se guarda (mayúsculas).
        $request->merge(['Nombre' => strtoupper(trim((string) $request->input('Nombre')))]);

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

        return redirect()->route('Producto')->with('success', 'Producto registrado correctamente.');
    }
}
