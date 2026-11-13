<?php

namespace App\Http\Controllers\Inventario\Almacen;

use Illuminate\Http\Request;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Division;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;


class AlmacenController extends Controller
{
    public function consultar(Request $request)
    {
        if (! (Auth::check() && auth()->user()->id_tipoempleado == 1)) {
            return redirect('/');
        }

        $buscar = strtoupper(trim((string) $request->get('texto', '')));

        $query = DB::table('Almacen');
        if ($buscar !== '') {
            $query->where(function ($w) use ($buscar) {
                $w->where('Nombre', 'like', '%' . $buscar . '%');
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

        $Almacen = $query->paginate(20)->withQueryString();

        return view('Inventario.Almacen.ConsultarAlmacen', compact('Almacen'));
    }

    public function editar($id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            $Almacen=Almacen::findOrFail($id);
            return view('Inventario.Almacen.EditarAlmacen',compact('Almacen'));
        }
        else
        {
            return redirect('/');
        }
    }

    public function Actualizar(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
                    $validated = $request->validate([
                    'Nombre' => 'required|min:2|max:100'
                ]);

                $ActualizarAlmacen= DB::table('Almacen')
                ->where('id', $request['Id'])
                ->update(['Nombre' => strtoupper(trim($request['Nombre'])),
                        'Estado'=>$request['Estado']]);

                return redirect()->route('Almacen')->with('success', 'Almacén actualizado correctamente.');
        }
        else
        {
            return redirect('/');
        }

    }

    public function Crear()
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
             return view('Inventario.Almacen.CrearAlmacen');
        }
        else
        {
            return redirect('/');
        }
    }

    public function Guardar(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado==1)
        {
            // Se normaliza antes de validar para que la comprobación de duplicados compare igual que se guarda (mayúsculas).
            $request->merge(['Nombre' => strtoupper(trim((string) $request->input('Nombre')))]);

            $validated = $request->validate([
                'Nombre' => 'required|unique:Almacen|min:2|max:100'
            ]);

            $save= new Almacen();
            $save->Nombre=strtoupper(trim( $request['Nombre']));
            $save->Estado=$request['Estado'];
            $save->IdEmpleado=auth()->user()->id;
            $save->save();

            return redirect()->route('Almacen')->with('success', 'Almacén registrado correctamente.');
        }
        else
        {
            return redirect('/');
        }
    }
}
