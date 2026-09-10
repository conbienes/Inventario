<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Inventario\Inventario;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\Movimientos;
use App\Services\PayUService\Exception;

class ArqueoImport implements ToCollection
{    

    private $numRows = 0;
  

    public function collection(Collection $rows)
    {
       
        try {
            $i=0;        
            foreach ($rows as $row) 
            {
                
                if ($i>0) 
                {
                    $Excel=$rows[$i]; 
                   
                    if ($Excel[0]>0 && !is_null($Excel[7]) && is_numeric($Excel[7])) 
                    {
                        
                        $BuscarCantidades = DB::table('Inventario')                        
                        ->where('Id',$Excel[0])->get();
                       
                        foreach ($BuscarCantidades as $item) 
                        {     
                            if ($item->Cantidades != $Excel[7]) 
                            {     
                                ++$this->numRows;
                                
                                if ($Excel[8]>0 && $Excel[9]>0 && is_numeric($Excel[8])&& is_numeric($Excel[9]) )
                                {
                                     $Importar= DB::table('inventario')
                                    ->where('Id', $Excel[0])
                                    ->update(['Cantidades'=>$Excel[7],'CantidadMin' =>$Excel[9],'CantidadMax' =>$Excel[10]]);
                                }
                                else
                                {																
                                     $Importar= DB::table('inventario')
                                    ->where('Id', $Excel[0])
                                    ->update(['Cantidades'=>$Excel[7]]);
                                }
                                  
                                
                                $CrearP= new Movimientos();
                                $CrearP->Cantidad= $Excel[7];
                                $CrearP->IdTipoMovimiento= 3;
                                $CrearP->Descripcion= strtoupper($Excel[8]);
                                $CrearP->IdEmpleado=auth()->user()->id;   
                                $CrearP->IdArea= auth()->user()->id_area; 
                                $CrearP->IdSolicitante= auth()->user()->id;   
                                $CrearP->IdInventario= $Excel[0];           
                                $CrearP->save(); 
    
                            }
                        }
                        
                    }	
                    
                    if($Excel[9]>0 && $Excel[10]>0 && is_null($Excel[7])&& is_numeric($Excel[9])&& is_numeric($Excel[10]) )
                    {
                         $BuscarCantidades = DB::table('Inventario')                        
                        ->where('Id',$Excel[0])->get();
                        
                        foreach ($BuscarCantidades as $item) 
                        {                               
                                ++$this->numRows;														
                                $Importar= DB::table('inventario')
                                ->where('Id', $Excel[0])
                                ->update(['CantidadMin' =>$Excel[9],'CantidadMax' =>$Excel[10]]);
                                
                            
                        }
                        
                    }												
                }                       
               ++$i;                         
            }      
      
        } catch (\Exception $e) {

            return $e->getMessage();
        }               
    }

    public function getRowCount(): int
    {
       
        return $this->numRows;
    }
}