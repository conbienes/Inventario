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
        $buscar=strtoupper(trim($request->get('texto')));  

        $Proveedor = DB::table('Proveedor')->where("Nombre",'like',$buscar."%")
        ->orwhere("Nit",'like',"%".$buscar."%")->paginate(20); 
      
        return view('Inventario.Proveedor.ConsultarProveedor',compact('Proveedor','buscar'));
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

        $Proveedor = DB::table('Proveedor')->paginate(20); 
      
        return view('Inventario.Proveedor.ConsultarProveedor',compact('Proveedor')); 
         
    }

    public function Crear()
    {                  
      return view('Inventario.Proveedor.CrearProveedor');         
    }

    public function Guardar(Request $request)
    {        
       
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

       $Proveedor = DB::table('Proveedor')->paginate(20); 
      
        return view('Inventario.Proveedor.ConsultarProveedor',compact('Proveedor'));   
    }
}
