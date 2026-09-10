<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class UsersExport implements FromView
{

    use Exportable;
    public function __construct(Request $request)
    {
        $this->request = $request;
        
    }
    public function view(): View
    {
        
        $ini = strtotime($this->request->get('FechaI'));
        $ini = date('Y-m-d H:i:s', $ini);
        $fin = strtotime($this->request->get('FechaF').'23:59:00');
        $fin = date('Y-m-d H:i:s', $fin);
        
            $info = DB::table('regtickets')
                ->join('empleados',  'empleados.id','=','regtickets.id_empleado')
                ->join('restaurantes','restaurantes.id' , '=', 'regtickets.id_restaurante')
                ->join('divisiones','divisiones.id' , '=', 'empleados.id_div')
                ->select( 'regtickets.id','empleados.cedula','empleados.nombre as Nombres','empleados.apellidos','divisiones.nombre as div','restaurantes.nombre as Restaurante','regtickets.created_at')
                ->where('regtickets.created_at','>=', date($ini))
                ->where('regtickets.created_at', '<=',date($fin))
                ->orderBy('regtickets.id')
                ->get();

            $ini = $this->request->get('FechaI');
            $fin = $this->request->get('FechaF');
             
            return view('Informes.Excel',compact('info','ini','fin'));  
    }
}
