<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Restaurante;
use App\Models\Admin\Ticket;
use App\Models\Admin\Empleado;
use App\Models\Admin\Division;
use Carbon\Carbon;
use App\Http\Requests\ActualizarTicketRequest;
use Illuminate\Support\Facades\DB;

class RegTicketController extends Controller
{
   
    public function index()
    {
        return view('regTicket.index',[
            'restaurante'=> Restaurante::all(),
            'empleado'=>Empleado::all()            
        ]);
    }

    public function crear($emp,$rest)
    {   
        
        if (Auth()->id()==$emp) {
            $restaurante = Restaurante::findOrFail($rest);
            $empleado = Empleado::findOrFail($emp);
            $div=Division::all();
        
            return view('regTicket.registrar',compact('restaurante','empleado','div'));
        }
        else
        {
             abort(404, 'Page Not Found');
        }
     }

    public function registrar($rest,Request $request )
    {   
       
                $hoy=now()->toDateString();

                $ticket = Ticket::where('id_empleado','=',auth()->id())
                            ->whereDate('created_at','=',$hoy)
                            ->get();
               
                if (count($ticket)!=0) {
                    
                    foreach ($ticket as $item)
                    {
                    return redirect('/home')->with('mensaje', 'El ticket ya ha sido registrado el día de hoy con el numero'.$item->id);                
                    }
               
                }
                else
                {
                    $save= new Ticket();
                    $save->id_empleado= auth()->id();
                    $save->id_restaurante=$rest;
                    $save->id_estado=1;
                    $save->save();
                   
                    $ticket = Ticket::where('id_empleado','=',auth()->id())
                            ->orderby('created_at','DESC')->first();
                     
                          
                    $restaurantes = Restaurante::all();
                    $restaurante = Restaurante::findOrFail($rest);
                    $empleado = Empleado::find(auth()->id());
                   
                    $hora=Carbon::createFromDate();

                    return view('regTicket.Crear',[
                        'restaurante'=> $restaurante,
                        'empleado'=>$empleado,
                        'restaurantes'=>$restaurantes,
                        'ticket'=> $ticket]);
                    }
               
        
    }
    

    public function buscar(Request $request)
    {
        if($request['id_estado']>0)
        {
            $buscar = DB::table('regtickets')
            ->join('empleados',  'empleados.id','=','regtickets.id_empleado')
            ->join('restaurantes','restaurantes.id' , '=', 'regtickets.id_restaurante')
            ->select('regtickets.id','empleados.cedula','empleados.nombre as Nombres','empleados.apellidos','restaurantes.nombre as Restaurante','regtickets.created_at as fecha')
            ->where('regtickets.id','=',$request['id_estado'])
            ->get();
            return view('regTicket.anular',compact('buscar'));
        }
        else{
            $buscar='';
            return view('regTicket.anular',compact('buscar'));

        }
    }

    public function anular()
    {
        $buscar='';
        return view('regTicket.anular',compact('buscar'));
    }

    public function actualizar($id)
    {
            $hora=Carbon::createFromDate();
            $actualizar = DB::table('regtickets')
              ->where('id','=' ,$id)
              ->update(['id_estado' => 3, 'id_anulado'=>auth()->user()->id ,'updated_at'=>$hora]);
          
        return redirect('/home')->with('succes', 'El ticket numero '.$id.' fue anulado con exito');
    }

    public function reimprimir($id)
    {
            $buscar = DB::table('regtickets')
            ->where('regtickets.id','=',$id)
            ->get();
         
            foreach ($buscar as $item) {
              
                $restaurante =  Restaurante::findOrFail($item->id_restaurante);
                $empleado =     Empleado::findOrFail($item->id_empleado);
                $restaurantes = Restaurante::all();
                $ticket = Ticket::findOrFail($id);
                
            }
        
            return view('regTicket.Crear',[
                'restaurante'=> $restaurante,
                'restaurantes'=>$restaurantes,
                'empleado'=>$empleado,
                'ticket'=> $ticket]);
         
    }
   
}
