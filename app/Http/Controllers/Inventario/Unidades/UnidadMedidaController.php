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
        $buscar=strtoupper(trim($request->get('texto')));        
        $UnidadesM = DB::table('unidad_medida')->where("Nombre",'like',$buscar."%")
        ->orwhere("Abreviatura",'like',"%".$buscar."%")->paginate(6); 
      
        return view('Inventario.UnidadesMedidas.ConsultarMedidas',compact('UnidadesM'));
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

        $UnidadesM = DB::table('unidad_medida')->paginate(6); 
      
        return view('Inventario.UnidadesMedidas.ConsultarMedidas',compact('UnidadesM')); 
         
    }

    public function Crear()
    {                  
      return view('Inventario.UnidadesMedidas.CrearMedidas');         
    }

    public function Guardar(Request $request)
    {              
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
        
        $request->session()->flash('succes', 'Se registro unidad de registro');

        $UnidadesM = DB::table('unidad_medida')->paginate(6); 
              
       
        return view('Inventario.UnidadesMedidas.ConsultarMedidas',compact('UnidadesM'));   
    }
}
