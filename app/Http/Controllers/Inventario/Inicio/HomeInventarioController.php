<?php

namespace App\Http\Controllers\Inventario\Inicio;

use Illuminate\Http\Request;
use App\Models\Inventario\solicitudPedidos;
use App\Models\Inventario\Movimientos;
use App\Models\Inventario\Inventario;
use App\Models\Inventario\TipoMov;
use App\Models\Inventario\Empleado;
use App\Models\Inventario\Area;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Mail\ProdAprob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class HomeInventarioController extends Controller
{
    public function Inicio()
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect('/');
        }

        $user = auth()->user();
        $solicitudPedidos = solicitudPedidos::where('IdSolicita', $user->id)
            ->where('Estado', '1')
            ->get();
        $Aprobadas = solicitudPedidos::where('IdSolicita', $user->id)
            ->where('Estado', '2')
            ->get();
        $Inventario = DB::table('Inventario')->whereRaw('CantidadMin > Cantidades')->get();

        // Si el usuario es de tipo empleado 1
        if ($user->id_tipoempleado == 1) {
            $pendientes = solicitudPedidos::where('Estado', '1')->get();
            $TotalAprobadas = solicitudPedidos::where('Estado', '2')->get();

            return view('Inicio.inicioInventario', compact('solicitudPedidos', 'Aprobadas', 'pendientes', 'TotalAprobadas', 'Inventario'));
        }

        // Para otros tipos de usuario
        return view('Inicio.inicioInventario', compact('solicitudPedidos', 'Aprobadas'));
    }

    public function Pendientes(Request $request)
    {
        if (Auth::check()) {
            $buscar = $request->get('date');

            if (!is_null($buscar)) {
                $pedidoinventario = DB::table('pedidoinventario')
                    ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                    ->select('pedidoinventario.IdSolicitud as Id', 'S.Estado', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'))
                    ->where('S.IdSolicita', '=', auth()->user()->id)
                    ->where('S.Estado', '=', 1)
                    ->where(DB::raw('DATE(S.created_at)'), '=', $buscar)
                    ->groupBy('Id', 'Dia', 'S.Estado')
                    ->paginate(6);
            } else {
                $pedidoinventario = DB::table('pedidoinventario')
                    ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                    ->select('pedidoinventario.IdSolicitud as Id', 'S.Estado', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'))
                    ->where('S.IdSolicita', '=', auth()->user()->id)
                    ->where('S.Estado', '=', 1)
                    ->groupBy('Id', 'Dia', 'S.Estado')
                    ->paginate(6);
            }

            return view('Inventario.SolicProduc.SolicPendientes', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }

    public function vista($id)
    {
        if (Auth::check()) {
            $id = Crypt::decrypt($id);

            $pedidoinventario = DB::table('pedidoinventario')->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')->join('empleados as E', 'E.Id', '=', 'S.IdSolicita')->join('Areas as A', 'A.Id', '=', 'S.IdArea')->select('S.Id', 'pedidoinventario.CantidadS as Cantidad', DB::raw('DATE(S.created_at) as Fecha'), 'Pro.Nombre as NombreP', 'E.Nombre as NombreE', 'E.Apellidos', 'A.Nombre')->where('pedidoinventario.IdSolicitud', '=', $id)->get();

            return view('Inventario.SolicProduc.VerSolicitudes', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }

    public function VistaAprobadas($id)
    {
        if (Auth::check()) {
            $id = Crypt::decrypt($id);

            $pedidoinventario = DB::table('pedidoinventario')->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')->join('empleados as E', 'E.Id', '=', 'S.IdSolicita')->join('empleados as Aprueba', 'Aprueba.Id', '=', 'S.IdAprueba')->join('Areas as A', 'A.Id', '=', 'S.IdArea')->select('pedidoinventario.Comentario', 'pedidoinventario.CantidadAp as CantAprobadas', 'S.Id', 'pedidoinventario.CantidadS as Cantidad', DB::raw('DATE(S.FecAprobacion) as Fecha'), 'Pro.Nombre as NombreProduc', 'E.Nombre as NombreEmp', 'E.Apellidos as ApellidoEmp', 'A.Nombre as AreaS', 'Aprueba.Nombre as NombreAprueba', 'Aprueba.Apellidos as ApellidosAprueba')->where('pedidoinventario.IdSolicitud', '=', $id)->get();

            return view('Inventario.SolicProduc.SolicitudesAprobadasEmpleado', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }

    public function Solicitudes(Request $request)
    {
        if (Auth::check()) {
            $buscar = strtoupper(trim($request->get('texto')));

            if (!empty($buscar)) {
                $pedidoinventario = DB::table('pedidoinventario')
                    ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                    ->join('empleados as E', 'E.Id', '=', 'S.IdSolicita')
                    ->select('E.Nombre', 'S.FechaTentativa as fecha', 'E.Apellidos', 'S.Id as Id', 'S.Estado', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'))
                    ->whereRaw("(E.Nombre LIKE '%" . $buscar . "%' OR E.Apellidos  LIKE '%" . $buscar . "%') and S.Estado = 1 ")
                    ->groupBy('E.Nombre', 'Id', 'Dia', 'S.Estado', 'E.Apellidos', 'fecha')
                    ->paginate(10);
            } else {
                $pedidoinventario = DB::table('pedidoinventario')->leftJoin('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')->leftJoin('empleados as E', 'E.Id', '=', 'S.IdSolicita')->select('E.Nombre', 'S.FechaTentativa as fecha', 'E.Apellidos', 'S.Id as Id', 'S.Estado', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'))->where('S.Estado', '=', '1')->groupBy('E.Nombre', 'Id', 'Dia', 'S.Estado', 'E.Apellidos', 'S.FechaTentativa')->paginate(20);
            }

            return view('Inventario.SolicProduc.SolicPorAprobar', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }

    public function Cumplir($id)
    {
        if (Auth::check()) {
            $id = Crypt::decrypt($id);

            $pedidoinventario = DB::table('pedidoinventario')->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')->join('empleados as E', 'E.Id', '=', 'S.IdSolicita')->join('Areas as A', 'A.Id', '=', 'S.IdArea')->select('pedidoinventario.CantidadAp as CantAprobadas', 'S.Id', 'pedidoinventario.CantidadS as Cantidad', 'Pro.Nombre as NombreProduc', 'E.Nombre as NombreEmp', 'E.Apellidos as ApellidoEmp', 'A.Nombre as AreaS', 'I.Cantidades As Cantidades', 'pedidoinventario.Id as IdPedido', 'pedidoinventario.IdInventario', 'E.Id as IdEmpleado', 'A.Id AS IdArea')->where('pedidoinventario.IdSolicitud', '=', $id)->orderBy('NombreProduc')->get();

            return view('Inventario.SolicProduc.VerSolicitadosAprobados', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }

    public function AprobarSolicitud(Request $request)
    {
        $Papeleria = 0;

        if (Auth::check()) {
            $Cantidad = count($request->IdPedido);

            for ($i = 0; $i < $Cantidad; $i++) {
                $inventario = DB::table('inventario')
                    ->where('Id', $request->IdInventario[$i])
                    ->select('Cantidades', 'IdCategoria')
                    ->get();

                foreach ($inventario as $item) {
                    $categoria = $item->IdCategoria;

                    if ($categoria == 17 && auth()->user()->id == 10) {
                        $Papeleria = $Papeleria + 1;
                    }
                }
            }

            if (auth()->user()->id == 10) {
                if ($Papeleria > 0) {
                    for ($i = 0; $i < $Cantidad; $i++) {
                        $pedidoinventario = DB::table('pedidoinventario')
                            ->where('Id', $request->IdPedido[$i])
                            ->update(['CantidadAp' => $request->Catidad[$i], 'Comentario' => strtoupper($request->Comentario[$i])]);

                        $inventario = DB::table('inventario')
                            ->where('Id', $request->IdInventario[$i])
                            ->select('Cantidades')
                            ->get();

                        $CrearP = new Movimientos();
                        $CrearP->Cantidad = $request->Catidad[$i];
                        $CrearP->IdTipoMovimiento = 1;
                        $CrearP->Descripcion = strtoupper($request->Comentario[$i]);
                        $CrearP->IdEmpleado = auth()->user()->id;
                        $CrearP->IdArea = $request->IdArea;
                        $CrearP->IdSolicitante = $request->IdEmpleado;
                        $CrearP->IdInventario = $request->IdInventario[$i];
                        $CrearP->save();

                        foreach ($inventario as $item) {
                            $SumCantidad = $item->Cantidades - $request->Catidad[$i];
                        }

                        $inventario = DB::table('inventario')
                            ->where('Id', $request->IdInventario[$i])
                            ->update(['Cantidades' => $SumCantidad]);
                    }

                    $solicitudpedidos = DB::table('solicitudpedidos')
                        ->where('Id', $request['IdSolicitud'])
                        ->update(['IdAprueba' => auth()->user()->id, 'FecAprobacion' => NOW(), 'Estado' => 2]);

                    $Correo = DB::table('pedidoinventario')
                        ->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')
                        ->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')
                        ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                        ->join('empleados as E', 'E.Id', '=', 'S.IdAprueba')
                        ->join('Areas as A', 'A.Id', '=', 'S.IdArea')
                        ->select('S.IdAprueba', 'pedidoinventario.Comentario', 'S.IdSolicita', 'pedidoinventario.CantidadAp as CantidadA', 'pedidoinventario.CantidadS as CantidadS', 'Pro.Nombre as NombreP', 'E.Nombre as NombreE', 'E.Apellidos', 'E.Correo as Correo', 'A.Nombre')
                        ->where('pedidoinventario.IdSolicitud', '=', $request['IdSolicitud'])
                        ->get();

                    foreach ($Correo as $item) {
                        $Empleado = Empleado::where('id', $item->IdAprueba)
                            ->select('nombre', 'apellidos', 'id_div')
                            ->get();
                        $EmpleadoSolicita = Empleado::where('id', $item->IdSolicita)
                            ->select('nombre', 'apellidos', 'Correo')
                            ->get();
                    }

                    foreach ($EmpleadoSolicita as $item) {
                        $mail = $item->Correo;
                        Session::flash('success', 'La solicitud fue aprobada al empleado ' . $item->nombre . ' ' . $item->apellidos . ' ');
                    }

                    Mail::to($mail)->send(new ProdAprob($Correo, $Empleado));

                    return Redirect::to('/Solicitudes');
                } else {
                    Session::flash('success1', 'No tiene permisos, solicite permisos al administrador');

                    return Redirect::to('/Solicitudes');
                }
            }

            if (auth()->user()->id == 77) {
                for ($i = 0; $i < $Cantidad; $i++) {
                    $pedidoinventario = DB::table('pedidoinventario')
                        ->where('Id', $request->IdPedido[$i])
                        ->update(['CantidadAp' => $request->Catidad[$i], 'Comentario' => strtoupper($request->Comentario[$i])]);

                    $inventario = DB::table('inventario')
                        ->where('Id', $request->IdInventario[$i])
                        ->select('Cantidades')
                        ->get();

                    $CrearP = new Movimientos();
                    $CrearP->Cantidad = $request->Catidad[$i];
                    $CrearP->IdTipoMovimiento = 1;
                    $CrearP->Descripcion = strtoupper($request->Comentario[$i]);
                    $CrearP->IdEmpleado = auth()->user()->id;
                    $CrearP->IdArea = $request->IdArea;
                    $CrearP->IdSolicitante = $request->IdEmpleado;
                    $CrearP->IdInventario = $request->IdInventario[$i];
                    $CrearP->save();

                    foreach ($inventario as $item) {
                        $SumCantidad = $item->Cantidades - $request->Catidad[$i];
                    }

                    $inventario = DB::table('inventario')
                        ->where('Id', $request->IdInventario[$i])
                        ->update(['Cantidades' => $SumCantidad]);
                }

                $solicitudpedidos = DB::table('solicitudpedidos')
                    ->where('Id', $request['IdSolicitud'])
                    ->update(['IdAprueba' => auth()->user()->id, 'FecAprobacion' => NOW(), 'Estado' => 2]);

                $Correo = DB::table('pedidoinventario')
                    ->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')
                    ->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')
                    ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                    ->join('empleados as E', 'E.Id', '=', 'S.IdAprueba')
                    ->join('Areas as A', 'A.Id', '=', 'S.IdArea')
                    ->select('S.IdAprueba', 'pedidoinventario.Comentario', 'S.IdSolicita', 'pedidoinventario.CantidadAp as CantidadA', 'pedidoinventario.CantidadS as CantidadS', 'Pro.Nombre as NombreP', 'E.Nombre as NombreE', 'E.Apellidos', 'E.Correo as Correo', 'A.Nombre')
                    ->where('pedidoinventario.IdSolicitud', '=', $request['IdSolicitud'])
                    ->get();

                foreach ($Correo as $item) {
                    $Empleado = Empleado::where('id', $item->IdAprueba)
                        ->select('nombre', 'apellidos', 'id_div')
                        ->get();

                    $EmpleadoSolicita = Empleado::where('id', $item->IdSolicita)
                        ->select('nombre', 'apellidos', 'Correo')
                        ->get();
                }

                foreach ($EmpleadoSolicita as $item) {
                    $mail = $item->Correo;
                    Session::flash('success', 'La solicitud fue aprobada al empleado ' . $item->nombre . ' ' . $item->apellidos . ' ');
                }

                Mail::to($mail)->send(new ProdAprob($Correo, $Empleado));

                return Redirect::to('/Solicitudes');
            }

            Session::flash('success1', 'No tiene permisos, solicite permisos al administrador');

            return Redirect::to('/Solicitudes');
        } else {
            return redirect('/');
        }
    }

    public function TotalAprobadas(Request $request)
    {
        try {
            if (!Auth::check()) {
                return redirect('/');
            }
            // Si el usuario solicita limpiar los filtros, elimina los valores de la sesión
            if ($request->query('limpiar')) {
                session()->forget(['FechaInicial', 'FechaFinal', 'Empleados']);
            }

            // Validar los datos del request utilizando Laravel Validation
            $validated = $request->validate([
                'FechaInicial' => 'nullable|date',
                'FechaFinal' => 'nullable|date|after_or_equal:FechaInicial',
                'Empleados' => 'nullable|exists:empleados,id', // Validar que el empleado existe en la tabla 'empleados'
            ]);

            // Obtener filtros desde la solicitud o la sesión
            $FechaI = $request->input('FechaInicial', session('FechaInicial'));
            $FechaF = $request->input('FechaFinal', session('FechaFinal'));
            $Empleados = $request->input('Empleados', session('Empleados'));

            // Guardar filtros en la sesión
            session([
                'FechaInicial' => $FechaI,
                'FechaFinal' => $FechaF,
                'Empleados' => $Empleados,
            ]);

            // Construir la consulta base
            $query = DB::table('pedidoinventario')->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')->join('empleados as E', 'E.id', '=', 'S.IdSolicita')->select('pedidoinventario.IdSolicitud as Id', 'S.Estado', 'E.nombre as Empleado', 'E.apellidos as Apellido', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'));

            // Aplicar filtros si existen
            if ($FechaI && $FechaF) {
                $query->whereBetween(DB::raw('DATE(S.created_at)'), [$FechaI, $FechaF]);
            }

            if ($Empleados) {
                $query->where('E.id', $Empleados);
            }

            // Agrupar y paginar resultados
            $pedidoinventario = $query->groupBy('Id', 'Dia', 'S.Estado', 'Empleado', 'Apellido')->paginate(7);

            return view('Inventario.SolicProduc.SolicitudesAprobadas', compact('pedidoinventario'));
        } catch (\Throwable $th) {
            // Manejo de la excepción
            return response()->json(
                [
                    'error' => 'Se ha producido un error. Por favor, inténtelo de nuevo más tarde.' . $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function Filtro(Request $request)
    {
        if (Auth::check()) {
            return view('Inventario.SolicProduc.FiltroSolicitudes');
        } else {
            return redirect('/');
        }
    }

    public function Aprobadas(Request $request)
    {
        if (Auth::check()) {
            $buscar = $request->get('date');

            if (!is_null($buscar)) {
                $pedidoinventario = DB::table('pedidoinventario')
                    ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                    ->join('empleados as E', 'E.id', '=', 'S.IdSolicita')
                    ->select('pedidoinventario.IdSolicitud as Id', 'S.Estado', 'E.nombre as Empleado', 'E.apellidos as Apellido', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'))
                    ->where('S.IdSolicita', '=', auth()->user()->id)
                    ->where('S.Estado', '=', 2)
                    ->where(DB::raw('DATE(S.created_at)'), '=', $buscar)
                    ->groupBy('Id', 'Dia', 'S.Estado', 'Empleado', 'Apellido')
                    ->paginate(8);
            } else {
                $pedidoinventario = DB::table('pedidoinventario')
                    ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                    ->join('empleados as E', 'E.id', '=', 'S.IdSolicita')
                    ->select('pedidoinventario.IdSolicitud as Id', 'E.nombre as Empleado', 'E.apellidos as Apellido', 'S.Estado', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'))
                    ->where('S.IdSolicita', '=', auth()->user()->id)
                    ->where('S.Estado', '=', 2)
                    ->groupBy('Id', 'Dia', 'S.Estado', 'Empleado', 'Apellido')
                    ->paginate(8);
            }
            return view('Inventario.SolicProduc.AprobadasEmpleado', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }
}
