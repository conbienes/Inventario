<?php

namespace App\Http\Controllers\Inventario\Arqueo;

use App\Imports\ArqueoImport;

use Illuminate\Http\Request;
use App\Services\Inventario\FiltrosInventario;
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
            // Va directo a la empresa con la que se está trabajando; se cambia desde el selector de la vista.
            $division = divisionInventarioActual();
            if ($division === null) {
                $Division = collect();
                return view('Inventario.Arqueo.InicioArqueo', compact('Division'));
            }

            session()->reflash(); // conserva los mensajes de éxito/error de la acción anterior

            return redirect()->route('Consultar_Arqueo_Empresa', ['id' => Crypt::encrypt($division)]);
        }
        else
        {
          return redirect('/');
        }
    }

    public function Consultar($id, Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            $id =  Crypt::decrypt($id);
            divisionInventarioActual((int) $id);

            $filtros = new FiltrosInventario((int) $id);
            $consulta = $filtros->aplicar($filtros->consulta(), $request);
            $Inventario = $filtros->ordenar($consulta, $request)->paginate(20)->withQueryString();
            $opciones = $filtros->opciones();

            return view('Inventario.Arqueo.ConsultarArqueo',compact('Inventario','opciones'));
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
            // Solo Excel/CSV razonables; antes no se validaba nada del archivo
            $request->validate([
                'Arqueos' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            ], [
                'Arqueos.required' => 'Debe seleccionar un archivo.',
                'Arqueos.mimes' => 'El archivo debe ser .xlsx, .xls o .csv.',
                'Arqueos.max' => 'El archivo no puede superar 5 MB.',
            ]);

            $import = new ArqueoImport();

            Excel::import($import , $request->file('Arqueos'));

            $numRows=['numRows'=>$import->getRowCount()];

            return redirect()->route('In_Arqueo')->with('succes', 'Se cargaron '.$numRows['numRows'].' datos');
        }
        else
        {
          return redirect('/');
        }
    }

}
