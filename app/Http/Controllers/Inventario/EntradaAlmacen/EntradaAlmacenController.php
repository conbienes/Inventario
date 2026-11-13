<?php

namespace App\Http\Controllers\Inventario\EntradaAlmacen;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Inventario\registro_producto;
use App\Models\Inventario\itemfactura;
use App\Models\Inventario\Division;
use App\Models\Inventario\Movimientos;
use App\Models\Admin\Empleado;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Mail\NotiEmpleado;

class EntradaAlmacenController extends Controller
{
    /**
     * Filtros comunes de las listas de pedidos (por procesar y aprobados):
     * desde / hasta (fecha de ingreso), valor_min / valor_max y orden.
     */
    private function filtrarRegistros($query, Request $request)
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'valor_min' => ['nullable', 'numeric', 'min:0'],
            'valor_max' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($request->filled('desde')) {
            $query->whereDate('registro_producto.FechaIngreso', '>=', $request->get('desde'));
        }
        if ($request->filled('hasta')) {
            $query->whereDate('registro_producto.FechaIngreso', '<=', $request->get('hasta'));
        }
        if ($request->filled('valor_min')) {
            $query->where('registro_producto.PrecioTotal', '>=', (float) $request->get('valor_min'));
        }
        if ($request->filled('valor_max')) {
            $query->where('registro_producto.PrecioTotal', '<=', (float) $request->get('valor_max'));
        }

        return match ((string) $request->get('orden', 'recientes')) {
            'antiguas' => $query->orderBy('registro_producto.FechaIngreso')->orderBy('registro_producto.Id'),
            'valor_desc' => $query->orderByDesc('registro_producto.PrecioTotal'),
            'valor_asc' => $query->orderBy('registro_producto.PrecioTotal'),
            'proveedor' => $query->orderBy('P.Nombre')->orderByDesc('registro_producto.FechaIngreso'),
            default => $query->orderByDesc('registro_producto.FechaIngreso')->orderByDesc('registro_producto.Id'),
        };
    }

    public function Inicio()
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            // Va directo a la empresa con la que se está trabajando; se cambia desde el selector de la vista.
            $division = divisionInventarioActual();
            if ($division === null) {
                $Division = collect();
                return view('Inventario.EntradaAlmacen.InicioEntradaAlmacen', compact('Division'));
            }

            session()->reflash(); // conserva los mensajes de éxito/error de la acción anterior

            return redirect()->route('Vista_EntAlmacen', ['id' => Crypt::encrypt($division)]);
        } else {
            return redirect('/');
        }
    }

    public function Vista($id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $id = Crypt::decrypt($id);
            divisionInventarioActual((int) $id);

            $Division = DB::table('divisiones')->where('id', $id)->get();

            // Pedidos por procesar, más recientes primero (con el proveedor para mostrarlo en el panel)
            $Activas = DB::table('registro_producto')
                ->leftJoin('Proveedor as P', 'P.Id', '=', 'registro_producto.IdProveedor')
                ->select('registro_producto.*', 'P.Nombre as Proveedor', 'P.Nit as Nit')
                ->where('registro_producto.Estado', '=', 1)
                ->where('registro_producto.IdDivisiones', '=', $id)
                ->orderByDesc('registro_producto.FechaIngreso')
                ->orderByDesc('registro_producto.Id')
                ->get();

            $Terminadas = DB::table('registro_producto')->where('Estado', '=', 2)->where('IdDivisiones', '=', $id)->get();

            return view('Inventario.EntradaAlmacen.VistaEntradaAlmacen', compact('Division', 'Activas', 'Terminadas'));
        } else {
            return redirect('/');
        }
    }

    public function EditarSolicitud($id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $id = Crypt::decrypt($id);

            $SoliPendi = DB::table('registro_producto')->join('Proveedor as P', 'P.Id', '=', 'registro_producto.IdProveedor')->join('estados as E', 'E.Id', '=', 'registro_producto.Estado')->select('P.Nombre as Proveedor', 'P.Nit as Nit', 'registro_producto.Id', 'registro_producto.PrecioTotal as PrecioTotal', 'registro_producto.Nfactura as Nfactura', 'registro_producto.OrdenPedido as OrdenPedido', 'registro_producto.FechaIngreso as FechaIngreso', 'registro_producto.Estado as Estados', 'E.Nombre as Estado', 'registro_producto.IdDivisiones as Div')->where('registro_producto.Id', '=', $id)->get();

            return view('Inventario.EntradaAlmacen.EditarEntradaInventario', compact('SoliPendi'));
        } else {
            return redirect('/');
        }
    }

    public function ActualizarPedido(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $validated = $request->validate([
                'Nfactura' => 'required|alpha_dash',
                'OrdenPedido' => 'required|alpha_dash',
            ]);

            $registro = DB::table('registro_producto')
                ->where('id', $request['Id'])
                ->update(['Nfactura' => strtoupper(trim($request['Nfactura'])), 'OrdenPedido' => strtoupper(trim($request['OrdenPedido']))]);

            return redirect()->route('EntAlmacen')->with('succes', 'La solicitud ' . $request['Id'] . ' fue actualizada correctamente');
        } else {
            return redirect('/');
        }
    }

    public function FVistaAprobadas($id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $id = Crypt::decrypt($id);

            $itemfactura = DB::table('itemfactura')->join('registro_producto as Pro', 'Pro.Id', '=', 'itemfactura.IdRegProduc')->join('inventario as I', 'I.Id', '=', 'itemfactura.IdInventario')->join('Producto as P', 'P.Id', '=', 'I.IdProducto')->select('P.Nombre as Producto', 'itemfactura.IdRegProduc as RegistroFactura', 'I.Id as Inventario', 'Pro.IdDivisiones as Div', 'itemfactura.CantidadA', 'itemfactura.CantidadS', 'itemfactura.CantidadA as Aprobadas', 'itemfactura.Comentario', 'itemfactura.Id', 'itemfactura.Completas')->where('itemfactura.IdRegProduc', '=', $id)->get();

            foreach ($itemfactura as $item) {
                $Div = $item->Div;
            }

            return view('Inventario.EntradaAlmacen.VistaItemAprobados', compact('itemfactura'));
        } else {
            return redirect('/');
        }
    }

    public function SolicAproba(Request $request, $id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $buscar = strtoupper(trim((string) $request->get('texto', '')));

            $div = Crypt::decrypt($id);

            // Solo pedidos terminados (Estado 2) de la division; la busqueda no debe salir de esa division.
            $query = DB::table('registro_producto')
                ->join('Proveedor as P', 'P.Id', '=', 'registro_producto.IdProveedor')
                ->join('estados as E', 'E.Id', '=', 'registro_producto.Estado')
                ->select('P.Nombre as Proveedor', 'P.Nit as Nit', 'registro_producto.Id', 'registro_producto.PrecioTotal as PrecioTotal', 'registro_producto.Nfactura as Nfactura', 'registro_producto.OrdenPedido as OrdenPedido', 'registro_producto.FechaIngreso as FechaIngreso', 'registro_producto.Estado as Estados', 'E.Nombre as Estado', 'registro_producto.IdDivisiones')
                ->where('registro_producto.IdDivisiones', '=', $div)
                ->where('registro_producto.Estado', '=', 2);

            if ($buscar != '') {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery
                        ->where('P.Nombre', 'like', '%' . $buscar . '%')
                        ->orWhere('P.Nit', 'like', '%' . $buscar . '%')
                        ->orWhere('registro_producto.OrdenPedido', 'like', '%' . $buscar . '%')
                        ->orWhere('registro_producto.Nfactura', 'like', '%' . $buscar . '%');
                });
            }

            $SoliPendi = $this->filtrarRegistros($query, $request)->paginate(20)->withQueryString();

            return view('Inventario.EntradaAlmacen.VistaAprobadas', compact('SoliPendi', 'div'));
        } else {
            return redirect('/');
        }
    }

    public function Crear($id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $id = Crypt::decrypt($id);

            $Division = DB::table('divisiones')->where('id', $id)->get();
            $Empleado = DB::table('Empleados')
                ->where('id', auth()->user()->id)
                ->get();

            return view('Inventario.EntradaAlmacen.CrearEntradaAlmacen', compact('Division', 'Empleado'));
        } else {
            return redirect('/');
        }
    }

    public function AñadirFactura(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $Factura[] = ['Proveedor' => $request['Proveedor'], 'NFactura' => $request['NFactura'], 'PrecioT' => $request['PrecioT'], 'OPedido' => $request['OPedido'], 'FechaI' => $request['FechaI'], 'Division' => $request['Division']];
            return view('Inventario.EntradaAlmacen.IngresarFactura', compact('Factura'));
        } else {
            return redirect('/');
        }
    }

    public function CrearFactura(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $validated = $request->validate([
                'IdInventario' => 'required',
            ]);

            $save = new registro_producto();
            $save->PrecioTotal = $request['PrecioT'];
            $save->IdProveedor = $request['Proveedor'];
            $save->Nfactura = $request['NFactura'];
            $save->Estado = 1;
            $save->IdEmpleado = auth()->user()->id;
            $save->IdDivisiones = $request['Division'];
            $save->IdTipoMov = 1;
            $save->OrdenPedido = $request['OPedido'];
            $save->FechaIngreso = $request['FechaI'];
            $save->save();

            $Datos = count($request['IdInventario']);

            if ($Datos > 0) {
                for ($i = 0; $i < $Datos; $i++) {
                    $ItemFact = new itemfactura();
                    $ItemFact->CantidadS = $request->Cantidad[$i];
                    $ItemFact->IdInventario = $request->IdInventario[$i];
                    // La columna es entera y admite NULL: un valor vacío no puede enviarse como cadena vacía
                    $valorLinea = $request->Valor[$i] ?? null;
                    $ItemFact->ValorUnidad = ($valorLinea === null || $valorLinea === '') ? null : (int) round((float) $valorLinea);
                    $ItemFact->IdRegProduc = $save->id;
                    $ItemFact->Completas = 0;
                    $ItemFact->CantidadA = 0;
                    $ItemFact->save();
                }
            }

            return redirect()->route('EntAlmacen')->with('succes', 'El pedido de compra se ingreso correctamente');
        } else {
            return redirect('/');
        }
    }

    public function SolicPend(Request $request, $id)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {

            $buscar = strtoupper(trim((string) $request->get('texto', '')));
            $div = Crypt::decrypt($id);

            // Consulta base compartida
            $query = DB::table('registro_producto')->join('Proveedor as P', 'P.Id', '=', 'registro_producto.IdProveedor')->join('estados as E', 'E.Id', '=', 'registro_producto.Estado')->select('P.Nombre as Proveedor', 'P.Nit as Nit', 'registro_producto.Id', 'registro_producto.PrecioTotal as PrecioTotal', 'registro_producto.Nfactura as Nfactura', 'registro_producto.OrdenPedido as OrdenPedido', 'registro_producto.FechaIngreso as FechaIngreso', 'registro_producto.Estado as Estados', 'E.Nombre as Estado', 'registro_producto.IdDivisiones')->where('registro_producto.IdDivisiones', '=', $div)->where('registro_producto.Estado', '=', 1);

            // Agregar búsqueda dinámica si existe un término de búsqueda
            if (!empty($buscar)) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery
                        ->orWhere('P.Nombre', 'like', "%$buscar%")
                        ->orWhere('P.Nit', 'like', "%$buscar%")
                        ->orWhere('registro_producto.OrdenPedido', 'like', "%$buscar%")
                        ->orWhere('registro_producto.Nfactura', 'like', "%$buscar%");
                });
            }

            $SoliPendi = $this->filtrarRegistros($query, $request)->paginate(20)->withQueryString();

            return view('Inventario.EntradaAlmacen.VistaPendientes', compact('SoliPendi', 'div'));

        } else {
            return redirect('/');
        }
    }

    public function AprobarFactura($id, $div)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {

            $div = Division::findOrFail($div);
            $Aprobar = Crypt::decrypt($id);

            $SoliPendi = DB::table('itemfactura')->join('inventario as I', 'I.Id', '=', 'itemfactura.IdInventario')->join('Producto as P', 'P.Id', '=', 'I.IdProducto')->select('P.Nombre as Producto', 'itemfactura.IdRegProduc as RegistroFactura', 'I.Id as Inventario', 'itemfactura.CantidadS as Cantidades', 'itemfactura.CantidadA as Aprobadas', 'itemfactura.Comentario', 'itemfactura.Id', 'itemfactura.Completas')->where('IdRegProduc', '=', $Aprobar)->get();

            return view('Inventario.EntradaAlmacen.AprobarCompras', compact('SoliPendi', 'div'));

        } else {
            return redirect('/');
        }
    }

    public function AprobarItems(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $estado = $request->Estado;
            $registroF = $request->RegistroFactura;
           

            if (is_null($request->IdItem)) {
                $Cantidad = 0;
            } else {
                $Cantidad = count($request->IdItem);
            }

            if ($estado == 1) {
                for ($i = 0; $i < $Cantidad; $i++) {
                    $itemfactura = DB::table('itemfactura')
                        ->where('Id', $request->IdItem[$i])
                        ->get();

                    foreach ($itemfactura as $item) {
                        $CantidadA = $item->CantidadA + $request->Catidad[$i];
                        $CantidadS = $item->CantidadS;
                        $Completas = $item->Completas;
                    }

                    if ($CantidadA == $CantidadS) {
                        if ($Completas == 0) {
                            $pedidoinventario = DB::table('itemfactura')
                                ->where('Id', $request->IdItem[$i])
                                ->update(['CantidadA' => $CantidadA, 'Completas' => 1, 'Comentario' => strtoupper('Cantidades completas')]);
                        }
                    } else {
                        $pedidoinventario = DB::table('itemfactura')
                            ->where('Id', $request->IdItem[$i])
                            ->update(['CantidadA' => $CantidadA, 'Comentario' => strtoupper($request->Comentario[$i])]);
                    }
                }

                $ItemActivos = DB::table('itemfactura')->where('IdRegProduc', $registroF)->where('Completas', '=', 0)->get();

                if (count($ItemActivos) > 0) {
                    return redirect()->route('EntAlmacen')->with('succes1', 'La orden de compra quedo activa');
                } else {
                    $FacturaItem = DB::table('itemfactura')->where('IdRegProduc', $registroF)->get();

                    foreach ($FacturaItem as $item) {
                        $ActualizaInventario = DB::table('inventario')
                            ->where('Id', $item->IdInventario)
                            ->get();

                        foreach ($ActualizaInventario as $act) {
                            $Actualizar = DB::table('inventario')
                                ->where('Id', $item->IdInventario)
                                ->update(['Cantidades' => $item->CantidadA + $act->Cantidades]);
                        }

                        $CrearP = new Movimientos();
                        $CrearP->Cantidad = $item->CantidadA;
                        $CrearP->IdTipoMovimiento = 10;
                        $CrearP->Descripcion = strtoupper($item->Comentario);
                        $CrearP->IdEmpleado = auth()->user()->id;
                        $CrearP->IdArea = auth()->user()->id_area;
                        $CrearP->IdSolicitante = auth()->user()->id;
                        $CrearP->IdInventario = $item->IdInventario;
                        $CrearP->save();
                    }

                    $pedidoinventario = DB::table('registro_producto')
                        ->where('Id', $registroF)
                        ->update(['Estado' => 2]);

                    return view('Inventario.EntradaAlmacen.CalificarProveedor', compact('registroF'));
                }
            } elseif ($estado == 2) {

                $Cantidad = count($request->IdItem);

                for ($i = 0; $i < $Cantidad; $i++) {
                    $itemfactura = DB::table('itemfactura')
                        ->where('Id', $request->IdItem[$i])
                        ->get();

                    foreach ($itemfactura as $item) {
                        $pedidoinventario = DB::table('itemfactura')
                            ->where('Id', $request->IdItem[$i])
                            ->update(['CantidadA' => $item->CantidadS, 'Completas' => 1, 'Comentario' => strtoupper('Cantidades completas')]);

                        $ActualizaInventario = DB::table('inventario')
                            ->where('Id', $request->Inventario[$i])
                            ->select('Cantidades')
                            ->get();

                        foreach ($ActualizaInventario as $act) {
                            $Actualizar = DB::table('inventario')
                                ->where('Id', $request->Inventario[$i])
                                ->update(['Cantidades' => $item->CantidadS + $act->Cantidades]);
                        }

                        $itemfactura3 = DB::table('itemfactura')
                            ->where('Id', $request->IdItem[$i])
                            ->get();

                        foreach ($itemfactura3 as $item) {
                            $CrearP = new Movimientos();
                            $CrearP->Cantidad = $item->CantidadA;
                            $CrearP->IdTipoMovimiento = 10;
                            $CrearP->Descripcion = strtoupper($item->Comentario);
                            $CrearP->IdEmpleado = auth()->user()->id;
                            $CrearP->IdArea = auth()->user()->id_area;
                            $CrearP->IdSolicitante = auth()->user()->id;
                            $CrearP->IdInventario = $request->Inventario[$i];
                            $CrearP->save();
                        }
                    }
                }

                $pedidoinventario = DB::table('registro_producto')
                    ->where('Id', $registroF)
                    ->update(['Estado' => 2]);

                return view('Inventario.EntradaAlmacen.CalificarProveedor', compact('registroF'));
            } elseif ($estado == 3) {
               


                for ($i = 0; $i < $Cantidad; $i++) {
                    $itemfactura = DB::table('itemfactura')
                        ->where('Id', $request->IdItem[$i])
                        ->get();
                    foreach ($itemfactura as $item) {
                        $CantidadA = $item->CantidadA + $request->Catidad[$i];
                        $CantidadS = $item->CantidadS;
                        $Completas = $item->Completas;
                    }

                    if ($Completas == 0) {
                        $pedidoinventario = DB::table('itemfactura')
                            ->where('Id', $request->IdItem[$i])
                            ->update(['CantidadA' => $CantidadA, 'Completas' => 1, 'Comentario' => strtoupper('Cantidades completas')]);
                    }
                }

                $FacturaItem = DB::table('itemfactura')->where('IdRegProduc', $registroF)->get();

                foreach ($FacturaItem as $item) {
                    $ActualizaInventario = DB::table('inventario')
                        ->where('Id', $item->IdInventario)
                        ->get();

                    foreach ($ActualizaInventario as $act) {
                        $Actualizar = DB::table('inventario')
                            ->where('Id', $item->IdInventario)
                            ->update(['Cantidades' => $item->CantidadA + $act->Cantidades]);
                    }

                    $CrearP = new Movimientos();
                    $CrearP->Cantidad = $item->CantidadA;
                    $CrearP->IdTipoMovimiento = 10;
                    $CrearP->Descripcion = strtoupper($item->Comentario);
                    $CrearP->IdEmpleado = auth()->user()->id;
                    $CrearP->IdArea = auth()->user()->id_area;
                    $CrearP->IdSolicitante = auth()->user()->id;
                    $CrearP->IdInventario = $item->IdInventario;
                    $CrearP->save();
                }

                $pedidoinventario = DB::table('registro_producto')
                    ->where('Id', $registroF)
                    ->update(['Estado' => 2]);

                return view('Inventario.EntradaAlmacen.CalificarProveedor', compact('registroF'));
            }
        } else {
            return redirect('/');
        }
    }

    public function Calificar(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $registroF = $request->registroF;

            $pedidoinventario = DB::table('registro_producto')
                ->where('Id', $registroF)
                ->update(['Comentario' => $request->CCalificacion, 'Calificacion' => $request->estrellas]);

            $Enviado = 0;

            return view('Inventario.EntradaAlmacen.NotificarEmpleado', compact('registroF', 'Enviado'));
        } else {
            return redirect('/');
        }
    }

    public function NotificarPersonal(Request $request)
    {
        if (Auth::check() && auth()->user()->id_tipoempleado == 1) {
            $registroF = $request->registroF;
            $Id = $request->Correo;

            $CorreoEmpleado = Empleado::select('Correo')->where('Id', '=', $Id)->get();

            $pedidoinventario = DB::table('registro_producto')->join('itemfactura as Item', 'Item.IdRegProduc', '=', 'registro_producto.Id')->join('inventario as I', 'I.Id', '=', 'Item.IdInventario')->join('producto as P', 'P.Id', '=', 'I.IdProducto')->select('P.Nombre as NombreP', 'Item.CantidadA as Cantidad')->where('registro_producto.Id', '=', $registroF)->get();

            foreach ($CorreoEmpleado as $item) {
                $Correo = $item->Correo;
                Mail::to($Correo)->send(new NotiEmpleado($pedidoinventario));
            }

            $Enviado = 1;

            return view('Inventario.EntradaAlmacen.NotificarEmpleado', compact('registroF', 'Enviado'));
        } else {
            return redirect('/');
        }
    }
}
