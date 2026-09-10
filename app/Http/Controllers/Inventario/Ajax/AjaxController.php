<?php

namespace App\Http\Controllers\Inventario\Ajax;

use Illuminate\Http\Request;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Categoria;
use App\Models\Inventario\Division;
use App\Models\Inventario\Empleado;
use App\Models\Inventario\Proveedor;
use App\Models\Inventario\Inventario;
use App\Models\Inventario\ubic_topografica;
use App\Models\BonoRegalo\ClienteBonoR;
use App\Models\BonoRegalo\TarjetaBonoR;
use App\Models\Inventario\Area;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AjaxController extends Controller
{
    public function buscarTarjetasBR(Request $request)
    {
        if (Auth::check()) {
            $tarjetas = [];

            if ($request->has('q')) {
                $Buscar = $request->q;

                $tarjetas = TarjetaBonoR::select('id', 'numero', 'valor')
                    ->where('numero', 'like', "%{$Buscar}%")
                    ->where('estado', 'activa') // Filtra solo las activas
                    ->get()
                    ->map(function ($tarjeta) {
                        return [
                            'id' => $tarjeta->id,
                            'text' => $tarjeta->numero,
                            'precio' => $tarjeta->valor,
                        ];
                    });
            }

            return response()->json($tarjetas);
        } else {
            return redirect('/');
        }
    }

    public function Division(Request $request)
    {
        if (Auth::check()) {
            $Division = [];

            $Buscar = $request->q;
            $Division = Division::select('Id', 'Nombre')
                ->where('Nombre', 'LIKE', "%$Buscar%")
                ->get();

            return response()->json($Division);
        } else {
            return redirect('/');
        }
    }

    public function Producto(Request $request)
    {
        if (Auth::check()) {
            $Producto = [];
            $Buscar = $request->q;
            $Producto = Producto::select('Id', 'Nombre')
                ->where('Nombre', 'LIKE', "%$Buscar%")
                ->get();

            return response()->json($Producto);
        } else {
            return redirect('/');
        }
    }

    public function topografica(Request $request)
    {
        if (Auth::check()) {
            $ubic_topografica = [];

            if ($request->has('q')) {
                $Buscar = $request->q;
                $ubic_topografica = ubic_topografica::select('Id', 'Nombre')
                    ->where('Nombre', 'LIKE', "%$Buscar%")
                    ->get();
            }
            return response()->json($ubic_topografica);
        } else {
            return redirect('/');
        }
    }

    public function Categoria(Request $request)
    {
        if (Auth::check()) {
            $Categoria = [];

            $Buscar = $request->q;
            $Categoria = Categoria::select('Id', 'Nombre')
                ->where('Nombre', 'LIKE', "%$Buscar%")
                ->get();

            return response()->json($Categoria);
        } else {
            return redirect('/');
        }
    }

    public function Almacen(Request $request)
    {
        if (Auth::check()) {
            $Almacen = [];
            $Buscar = $request->q;
            $Almacen = Almacen::select('Id', 'Nombre')
                ->where('Nombre', 'LIKE', "%$Buscar%")
                ->get();

            return response()->json($Almacen);
        } else {
            return redirect('/');
        }
    }

    public function Area(Request $request)
    {
        if (Auth::check()) {
            $Area = [];
            if ($request->has('q')) {
                $Buscar = $request->q;
                $Area = Area::select('Id', 'Nombre')
                    ->where('Nombre', 'LIKE', "%$Buscar%")
                    ->get();
            }

            return response()->json($Area);
        } else {
            return redirect('/');
        }
    }

    public function Empleado(Request $request)
    {
        if (Auth::check()) {
            $Empleado = [];
            if ($request->has('q')) {
                $Buscar = $request->q;
                $Empleado = Empleado::select()
                    ->where('Nombre', 'LIKE', "%$Buscar%")
                    ->orwhere('apellidos', 'LIKE', "%$Buscar%")
                    ->orwhere('cedula', 'LIKE', "%$Buscar%")
                    ->get();
            }

            return response()->json($Empleado);
        } else {
            return redirect('/');
        }
    }

    public function Empleados(Request $request)
    {
        if (Auth::check()) {
            $Empleado = [];
            if ($request->has('q')) {
                $Buscar = $request->q;
                $Empleado = Empleado::select()
                    ->where('Nombre', 'LIKE', "%$Buscar%")
                    ->orwhere('apellidos', 'LIKE', "%$Buscar%")
                    ->get();
            }

            return response()->json($Empleado);
        } else {
            return redirect('/');
        }
    }

    public function Inventario(Request $request)
    {
        if (Auth::check()) {
            $Inventario = [];

            if ($request->has('q')) {
                $Buscar = $request->q;

                $Inventario = Inventario::select('Inventario.Id as Id', 'Inventario.Cantidades as Cantidad', 'p.Nombre as Nombre', 'p.Codigo as Codigo')
                    ->join('Producto as p', 'p.Id', '=', 'Inventario.IdProducto')
                    ->join('Divisiones as d', 'd.Id', '=', 'Inventario.IdDivisiones')
                    ->where('p.Nombre', 'like', "%$Buscar%")
                    ->where('d.Id', '=', auth()->user()->id_div)
                    ->orderByDesc('Cantidad')
                    ->get();
            }

            return response()->json($Inventario);
        } else {
            return redirect('/');
        }
    }

    public function ClienteBonoR(Request $request)
    {
        if ($request->has('q')) {
            $buscar = trim($request->q);

            $clientes = ClienteBonoR::query()
                ->select([
                    'id',
                    'cedula',
                    'razons', // si tu columna es razon_social, usa: DB::raw('razon_social as razons')
                    'nombre',
                    'segundo_nombre',
                    'apellidos',
                    'segundo_apellido',
                ])
                ->where(function ($q) use ($buscar) {
                    $q->where('cedula', 'like', "%{$buscar}%")
                        ->orWhere('razons', 'like', "%{$buscar}%")
                        ->orWhereRaw("CONCAT_WS(' ', nombre, segundo_nombre, apellidos, segundo_apellido) LIKE ?", ["%{$buscar}%"]);
                })
                ->limit(20)
                ->get()
                ->map(function ($c) {
                    // Usa razón social si existe; si no, nombre completo
                    $display = $c->razons ?: trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([$c->nombre, $c->segundo_nombre, $c->apellidos, $c->segundo_apellido])))) ?: 'SIN NOMBRE';

                    return [
                        'id' => $c->id,
                        'text' => "{$c->cedula} - {$display}",
                    ];
                })
                ->values();

            return response()->json($clientes); // <-- array plano de {id, text}
        }
    }

    public function Proveedor(Request $request)
    {
        if (Auth::check()) {
            $Proveedor = [];
            if ($request->has('q')) {
                $Buscar = $request->q;
                $Proveedor = Proveedor::select()
                    ->where('Nombre', 'LIKE', "%$Buscar%")
                    ->orwhere('Nit', 'LIKE', "%$Buscar%")
                    ->get();
            }

            return response()->json($Proveedor);
        } else {
            return redirect('/');
        }
    }

    public function Inventario1(Request $request)
    {
        if (Auth::check()) {
            $Inventario = [];

            if ($request->has('q')) {
                $Buscar = $request->q;

                $Inventario = Inventario::select('Inventario.Id as Id', 'p.Nombre as Nombre', 'p.Codigo as Codigo')
                    ->join('Producto as p', 'p.Id', '=', 'Inventario.IdProducto')
                    ->join('Divisiones as d', 'd.Id', '=', 'Inventario.IdDivisiones')
                    ->where('p.Nombre', 'like', "%$Buscar%")
                    ->where('d.Id', '=', 1)
                    ->get();
            }

            return response()->json($Inventario);
        } else {
            return redirect('/');
        }
    }
    public function Inventario2(Request $request)
    {
        if (Auth::check()) {
            $Inventario = [];

            if ($request->has('q')) {
                $Buscar = $request->q;

                $Inventario = Inventario::select('Inventario.Id as Id', 'p.Nombre as Nombre', 'p.Codigo as Codigo')
                    ->join('Producto as p', 'p.Id', '=', 'Inventario.IdProducto')
                    ->join('Divisiones as d', 'd.Id', '=', 'Inventario.IdDivisiones')
                    ->where('p.Nombre', 'like', "%$Buscar%")
                    ->where('d.Id', '=', 2)
                    ->get();
            }

            return response()->json($Inventario);
        } else {
            return redirect('/');
        }
    }
    public function Inventario3(Request $request)
    {
        if (Auth::check()) {
            $Inventario = [];

            if ($request->has('q')) {
                $Buscar = $request->q;

                $Inventario = Inventario::select('Inventario.Id as Id', 'p.Nombre as Nombre', 'p.Codigo as Codigo')
                    ->join('Producto as p', 'p.Id', '=', 'Inventario.IdProducto')
                    ->join('Divisiones as d', 'd.Id', '=', 'Inventario.IdDivisiones')
                    ->where('p.Nombre', 'like', "%$Buscar%")
                    ->where('d.Id', '=', 3)
                    ->get();
            }

            return response()->json($Inventario);
        } else {
            return redirect('/');
        }
    }

    public function Correos(Request $request)
    {
        if (Auth::check()) {
            $Empleado = [];
            if ($request->has('q')) {
                $Buscar = $request->q;
                $Empleado = Empleado::select('Id', 'Correo')
                    ->where('Correo', 'LIKE', "%$Buscar%")
                    ->get();
            }

            return response()->json($Empleado);
        } else {
            return redirect('/');
        }
    }
}
