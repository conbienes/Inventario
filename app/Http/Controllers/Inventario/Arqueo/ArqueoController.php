<?php

namespace App\Http\Controllers\Inventario\Arqueo;

use App\Imports\ArqueoImport;

use Illuminate\Http\Request;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Categoria;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Models\Inventario\Inventario;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;



class ArqueoController extends Controller
{
    public function Inicio()
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            $Division = DB::table('divisiones')->get();
            return view('Inventario.Arqueo.InicioArqueo',compact('Division'));
        }
        else
        {
          return redirect('/');
        }
    }

    public function Consultar($id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            $id =  Crypt::decrypt($id);

            $Inventario = DB::table('Inventario')
            ->join('Producto as p','p.Id','=','Inventario.IdProducto')
            ->join('Divisiones as d','d.Id','=','Inventario.IdDivisiones')
            ->join('Almacen as a','a.Id','=','Inventario.IdAlmacen')
            ->join('categoria_inventario as c','c.Id','=','Inventario.IdCategoria')
            ->select('Inventario.Id','Inventario.Cantidades','Inventario.CantidadMin','Inventario.CantidadMax',
            'p.Nombre as Producto','d.Nombre as Division','a.Nombre as Almacen','c.Nombre as Categoria','d.Id as IdEmpresa')
            ->where("d.Id",'=',"$id")
            ->orderBy('Almacen','desc','Categoria','Producto')->paginate(10);

            return view('Inventario.Arqueo.ConsultarArqueo',compact('Inventario'));
        }
        else
        {
          return redirect('/');
        }
    }

    public function import(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            $import = new ArqueoImport();

            Excel::import($import , request()->file('Arqueos'));

            $numRows=['numRows'=>$import->getRowCount()];

            return redirect('/Arqueo')->with('succes', 'Se cargaron '.$numRows['numRows'].' datos');
        }
        else
        {
          return redirect('/');
        }
    }

}
