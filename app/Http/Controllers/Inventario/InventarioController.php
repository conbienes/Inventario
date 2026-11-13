<?php
namespace App\Http\Controllers\Inventario;

use Illuminate\Http\Request;
use App\Services\Inventario\FiltrosInventario;
use App\Models\Inventario\Division;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Inventario;
use App\Models\Inventario\Categoria;
use App\Models\Inventario\Movimientos;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class InventarioController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Verificar que el módulo seleccionado sea Bono Regalo (2)
            if (session('modulo_seleccionado') != 1) {
                abort(403, 'Acceso no autorizado para este módulo');
            }
            return $next($request);
        });
    }

    public function consultar($id, Request $request)
    {
        if (Auth::check()) {
            $id = Crypt::decrypt($id);
            $Division = Division::findOrFail($id);
            divisionInventarioActual((int) $id);

            $filtros = new FiltrosInventario((int) $id);
            $consulta = $filtros->aplicar($filtros->consulta(), $request);
            $Inventarios = $filtros->ordenar($consulta, $request)->paginate(20)->withQueryString();
            $opciones = $filtros->opciones();

            return view('Inventario.Inventarios.ConsultarInventario', compact('Inventarios', 'Division', 'opciones'));
        } else {
            return redirect('/');
        }
    }

    public function Producto($id)
    {
        if (Auth::check()) {
            $id = Crypt::decrypt($id);
            $div = 0;
            $Inventario = DB::table('Movimientos')->join('tipomovimiento as T', 'T.Id', '=', 'Movimientos.IdTipoMovimiento')->join('empleados as E', 'E.Id', '=', 'Movimientos.IdEmpleado')->join('Inventario as I', 'I.Id', '=', 'Movimientos.IdInventario')->join('Divisiones as d', 'd.Id', '=', 'I.IdDivisiones')->join('Producto as p', 'p.Id', '=', 'I.IdProducto')->select('Movimientos.Cantidad', 'T.Nombre', 'Movimientos.Descripcion', 'Movimientos.created_at as FCreacion', 'p.Nombre as Producto', 'd.Id as Division', 'E.Nombre as Empleado', 'E.Apellidos as Apellidos')->where('I.Id', '=', $id)->get();

            foreach ($Inventario as $item) {
                $div = $item->Division;
            }

            if ($div != 0) {
                return view('Inventario.Inventarios.InfoEditInven', compact('Inventario', 'div'));
            } else {
                Session::flash('success', 'Producto Sin Movimientos');
                return redirect()->back()->withErrors('')->withInput();
            }
        } else {
            return redirect('/');
        }
    }

    public function Inicio()
    {
        if ((Auth::check() && auth()->user()->id_tipoempleado == 1) || auth()->user()->id_tipoempleado == 3) {
            // Va directo a la empresa con la que se está trabajando; se cambia desde el selector de la vista.
            $division = divisionInventarioActual();
            if ($division === null) {
                $Division = collect();
                return view('Inventario.Inventarios.Inicio', compact('Division'));
            }

            session()->reflash(); // conserva los mensajes de éxito/error de la acción anterior

            return redirect()->route('Consultar_Inventario', ['id' => Crypt::encrypt($division)]);
        } else {
            return redirect('/');
        }
    }

    public function Actualizar(Request $request)
    {
        if (Auth::check()) {
            $validated = $request->validate([
                'Nombre' => 'required|min:2|max:255',
                'Abreviatura' => 'required|max:5',
            ]);

            $UnidadMedida = DB::table('Inventario')
                ->where('id', $request['Id'])
                ->update(['Nombre' => strtoupper(trim($request['Nombre'])), 'Abreviatura' => strtoupper(trim($request['Abreviatura'])), 'Estado' => $request['Estado']]);

            $Inventario = DB::table('Inventario')->paginate(10);

            return view('Inventario.Inventarios.ConsultarInventario', compact('Inventario'));
        } else {
            return redirect('/');
        }
    }

    public function Crear()
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            return view('Inventario.Inventarios.CrearInventario');
        } else {
            return redirect('/');
        }
    }

    public function Creara()
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $Division = Division::all();
            $Producto = Producto::all();
            $Almacen = Almacen::all();
            $Categoria = Categoria::all();

            return view('Inventario.Inventarios.CrearInventario', compact('Division', 'Producto', 'Almacen', 'Categoria'));
        } else {
            return redirect('/');
        }
    }

    public function Guardar(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $validated = $request->validate([
                'Cantidades' => 'required|numeric',
                'CantidadMin' => 'required|numeric',
                'CantidadMax' => 'required|numeric',
                'Producto' => 'required|numeric',
                'Division' => 'required|numeric',
                'Almacen' => 'required|numeric',
                'Categoria' => 'required|numeric',
                'Estanteria' => 'required',
                'Entrepaño' => 'required',
                'Gaveta' => 'required',
            ]);

            $buscar = DB::table('Inventario')->where('IdProducto', $request['Producto'])->where('IdDivisiones', $request['Division'])->where('IdAlmacen', $request['Almacen'])->where('IdCategoria', $request['Categoria'])->get();

            if (count($buscar) >= 1) {
                foreach ($buscar as $item) {
                    Session::flash('succes1', 'El producto ya existe en el inventario con el codigo ' . $item->Id . ' ');
                }

                return redirect()->route('Inventario');
            } else {
                $save = new Inventario();
                $save->Cantidades = $request['Cantidades'];
                $save->CantidadMin = $request['CantidadMin'];
                $save->CantidadMax = $request['CantidadMax'];
                $save->IdProducto = $request['Producto'];
                $save->IdDivisiones = $request['Division'];
                $save->Estanteria = $request['Estanteria'];
                $save->Entrepano = $request['Entrepaño'];
                $save->Gaveta = $request['Gaveta'];
                $save->IdAlmacen = $request['Almacen'];
                $save->IdCategoria = $request['Categoria'];
                $save->Estado = 1;
                $save->IdEmpleado = auth()->user()->id;
                $save->save();

                Session::flash('succes', 'Se creo el producto con el Codigo ' . $save->id . ' ');

                return redirect()->route('Inventario');
            }
        } else {
            return redirect('/');
        }
    }
}
