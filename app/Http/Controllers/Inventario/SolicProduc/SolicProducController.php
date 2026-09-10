<?php

namespace App\Http\Controllers\Inventario\SolicProduc;

use Illuminate\Http\Request;
use App\Models\Inventario\solicitudPedidos;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Categoria;
use App\Models\Inventario\pedidoinventario;
use App\Models\Inventario\Division;
use App\Models\Admin\Area;
use App\Models\Seguridad\Usuario;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Mail\NotifyMail;
use Illuminate\Support\Facades\Log;
use App\Mail\EnvioProd;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class SolicProducController extends Controller
{
    public function IngProducto(Request $request)
    {
        if (Auth::check()) {
            return view('Inventario.SolicProduc.AddSolicProduct');
        } else {
            return redirect('/');
        }
    }

    public function Crear()
    {
        if (Auth::check()) {
            $Area = Area::where('id', auth()->user()->id_area)->get();
            $Empleado = Usuario::where('id', auth()->user()->id)->get();

            return view('Inventario.SolicProduc.CrearSolicProduc', compact('Area', 'Empleado'));
        } else {
            return redirect('/');
        }
    }

    public function Creara()
    {
        if (Auth::check()) {
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
        try {
            ini_set('max_execution_time', 300);

            if (Auth::check()) {
                $rules = [
                    'IdInventario' => 'required',
                ];

                $messages = [
                    'IdInventario.required' => 'Al menos un producto es requerido',
                ];

                $this->validate($request, $rules, $messages);

                $CrearP = new solicitudPedidos();
                $CrearP->IdSolicita = $request['IdSolicitante'];
                $CrearP->IdArea = $request['id_area'];
                $CrearP->FechaTentativa = $request['Fecha'];
                $CrearP->Observaciones = $request['Observaciones'];
                $CrearP->Estado = 1;
                $CrearP->save();

                $Cantidad = count($request->IdInventario);

                for ($i = 0; $i < $Cantidad; $i++) {
                    $Pedido = new pedidoinventario();
                    $Pedido->IdInventario = $request->IdInventario[$i];
                    $Pedido->CantidadS = $request->Cantidad[$i];
                    $Pedido->IdSolicitud = $CrearP->id;
                    $Pedido->save();
                }

                $pedidoinventario = DB::table('pedidoinventario')
                    ->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')
                    ->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')
                    ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                    ->join('empleados as E', 'E.Id', '=', 'S.IdSolicita')
                    ->join('Areas as A', 'A.Id', '=', 'S.IdArea')
                    ->select('S.Id', 'pedidoinventario.CantidadS as Cantidad', 'Pro.Nombre as NombreP', 'E.Nombre as NombreE', 'E.Apellidos', 'A.Nombre', 'S.FechaTentativa', 'S.Observaciones')
                    ->where('pedidoinventario.IdSolicitud', '=', $CrearP->id)
                    ->get();

                $Observacion = DB::table('pedidoinventario')
                    ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                    ->select('S.Observaciones')
                    ->where('pedidoinventario.IdSolicitud', '=', $CrearP->id)
                    ->limit(1)
                    ->get();

                $FechaT = DB::table('solicitudpedidos')
                    ->select('FechaTentativa')
                    ->where('Id', '=', $CrearP->id)
                    ->get();

                $Area = Area::where('id', auth()->user()->id_area)->get();

                $Empleado = Usuario::where('id', auth()->user()->id)->get();

                foreach ($Empleado as $item) {
                    Mail::to($item->Correo)->send(new EnvioProd($pedidoinventario, $Empleado, $Area, $FechaT, $Observacion));
                }

                //$destinatario = ['almacen@conbienes.com' , 'tatianao@conbienes.com','alexanderq@conbienes.com'];

                $destinatario = ['alexanderq@conbienes.com'];

                Mail::to($destinatario)->send(new NotifyMail($pedidoinventario, $Empleado, $Area, $FechaT, $Observacion));

                return redirect('/homeInventario')->with('success', 'Se envio solicitud de pedido');

            } else {
                return redirect('/');
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar correos: ' . $e->getMessage());
        }
    }
}
