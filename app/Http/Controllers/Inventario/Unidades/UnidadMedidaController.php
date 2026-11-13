<?php

namespace App\Http\Controllers\Inventario\Unidades;

use Illuminate\Http\Request;
use App\Models\Inventario\UnidadMedida;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UnidadMedidaController extends Controller
{
    public function consultar(Request $request)
    {
        $buscar = strtoupper(trim((string) $request->get('texto', '')));

        $query = DB::table('unidad_medida');
        if ($buscar !== '') {
            $query->where(function ($w) use ($buscar) {
                $w->where('Nombre', 'like', '%' . $buscar . '%')->orWhere('Abreviatura', 'like', '%' . $buscar . '%');
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

        $UnidadesM = $query->paginate(20)->withQueryString();

        return view('Inventario.UnidadesMedidas.ConsultarMedidas', compact('UnidadesM'));
    }

    public function editar($id)
    { 
            $UnidadMedida = UnidadMedida::findOrFail($id);

            return view('Inventario.UnidadesMedidas.EditarMedidas',compact('UnidadMedida'));
         
    }

    public function Actualizar(Request $request)
    {         
            $validated = $request->validate([
            'Nombre' => 'required|min:2|max:255',
            'Abreviatura' => 'required|max:5',
        ]);
       
        $UnidadMedida= DB::table('unidad_medida')
        ->where('id', $request['Id'])
        ->update(['Nombre' => strtoupper(trim($request['Nombre'])),
                'Abreviatura'=> strtoupper(trim( $request['Abreviatura'])),
                'Estado'=>$request['Estado']]);

        return redirect()->route('UnidMedida')->with('success', 'Unidad de medida actualizada correctamente.');
         
    }

    public function Crear()
    {                  
      return view('Inventario.UnidadesMedidas.CrearMedidas');         
    }

    public function Guardar(Request $request)
    {
        // Se normaliza antes de validar para que la comprobación de duplicados compare igual que se guarda (mayúsculas).
        $request->merge(['Nombre' => strtoupper(trim((string) $request->input('Nombre')))]);

        $validated = $request->validate([
            'Nombre' => 'required|unique:unidad_medida|min:2|max:255',
            'Abreviatura' => 'required|max:5',
        ]);
        
        $save= new UnidadMedida();
        $save->Nombre=strtoupper(trim( $request['Nombre']));
        $save->Abreviatura=strtoupper(trim( $request['Abreviatura']));
        $save->Estado=$request['Estado'];   
        $save->IdEmpleado=auth()->user()->id;    
        $save->save();
        
        return redirect()->route('UnidMedida')->with('success', 'Unidad de medida registrada correctamente.');
    }
}
