<?php
namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;
use app\Imports\ArqueoImport;

class ArqueoExport implements FromView
{
    use Exportable;
    public function __construct(Request $request)
    {
        $this->request = $request;        
    }

    public function view(): View
    {
        $id = $this->request->get('IdEmpresa'); 
        $Inventario = DB::table('Inventario')
        ->join('Producto as p','p.Id','=','Inventario.IdProducto')
        ->join('Divisiones as d','d.Id','=','Inventario.IdDivisiones')
        ->join('Almacen as a','a.Id','=','Inventario.IdAlmacen')
        ->join('categoria_inventario as c','c.Id','=','Inventario.IdCategoria')
        ->select('Inventario.Id','p.Codigo','Inventario.Cantidades','Inventario.CantidadMin','Inventario.CantidadMax',
        'p.Nombre as Producto','d.Nombre as Division','a.Nombre as Almacen','c.Nombre as Categoria','d.Id as IdEmpresa','Inventario.Estanteria AS Estanteria','Inventario.Entrepano AS Entrepano','Inventario.Gaveta AS Gaveta')              
        ->where("d.Id",'=',"$id")
        ->orderBy('Producto')->get();             
        

        return view('Informes.ArqueoExcel',compact('Inventario'));  
    }   
}












