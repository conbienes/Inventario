<?php

namespace App\Http\Controllers\Inventario\Proveedor;

use Illuminate\Http\Request;
use App\Models\Inventario\Proveedor;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ProveedorController extends Controller
{
    public function consultar(Request $request)
    {
        $buscar = strtoupper(trim((string) $request->get('texto', '')));

        $query = DB::table('Proveedor');
        if ($buscar !== '') {
            $query->where(function ($w) use ($buscar) {
                $w->where('Nombre', 'like', '%' . $buscar . '%')->orWhere('Nit', 'like', '%' . $buscar . '%') ->orWhere('Correo', 'like', '%' . $buscar . '%') ->orWhere('Telefono', 'like', '%' . $buscar . '%');
            });
        }

        match ((string) $request->get('orden', 'nombre')) {
            'nombre_desc' => $query->orderByDesc('Nombre'),
            'recientes' => $query->orderByDesc('created_at')->orderByDesc('Id'),
            default => $query->orderBy('Nombre'),
        };

        $Proveedor = $query->paginate(20)->withQueryString();

        return view('Inventario.Proveedor.ConsultarProveedor', compact('Proveedor', 'buscar'));
    }

    public function editar($id)
    { 
            $Proveedor = Proveedor::findOrFail($id);

            return view('Inventario.Proveedor.EditarProveedor',compact('Proveedor'));
         
    }

    public function Actualizar(Request $request)
    {         
            $validated = $request->validate([
            'Nombre' => 'required|min:2|max:255',
            'Nit' => 'required|numeric',
        ]);
       
        $ProveedorActualizar= DB::table('Proveedor')
        ->where('id', $request['Id'])
        ->update(['Nombre' => strtoupper(trim($request['Nombre'])),
                'Nit'=> strtoupper(trim( $request['Nit'])), 'Telefono'=>$request['Telefono'],'Correo'=>strtoupper(trim( $request['Correo']))
                ]);

        return redirect()->route('Proveedor')->with('success', 'Proveedor actualizado correctamente.');
         
    }

    public function Crear()
    {                  
      return view('Inventario.Proveedor.CrearProveedor');         
    }

    public function Guardar(Request $request)
    {
        // Se normaliza antes de validar para que la comprobación de duplicados compare igual que se guarda (mayúsculas).
        $request->merge(['Nombre' => strtoupper(trim((string) $request->input('Nombre')))]);

        $validated = $request->validate([
            'Nombre' => 'required|unique:Proveedor|min:2|max:255',
            'Nit' => 'required|numeric|min:2',
        ]);

        $save= new Proveedor();
        $save->Nombre=strtoupper(trim( $request['Nombre']));
        $save->Nit=$request['Nit'];  
        $save->Telefono=$request['Telefono']; 
        $save->Correo=strtoupper(trim($request['Correo']));  
        $save->IdEmpleado=auth()->user()->id;
        $save->save();

        return redirect()->route('Proveedor')->with('success', 'Proveedor registrado correctamente.');
    }
}
