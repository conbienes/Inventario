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

    /**
     * Crea una solicitud de productos (pedido) con sus líneas.
     * - El solicitante y el área salen de la sesión, nunca del formulario.
     * - Los productos deben pertenecer a la división del usuario.
     * - Cabecera y líneas se guardan en una sola transacción.
     * - Si el correo falla, el pedido sigue guardado y se avisa.
     */
    public function Guardar(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $usuario = auth()->user();

        if (empty($usuario->id_area)) {
            return back()->withInput()->withErrors(['general' => 'Su usuario no tiene un área asignada. Solicite al administrador que la configure.']);
        }

        $request->validate([
            'Fecha' => 'required|date|after_or_equal:today',
            'Observaciones' => 'nullable|string|max:500',
            'IdInventario' => 'required|array|min:1|max:50',
            'IdInventario.*' => 'required|integer|distinct',
            'Cantidad' => 'required|array|min:1|max:50',
            'Cantidad.*' => 'required|integer|min:1|max:100000',
        ], [
            'Fecha.required' => 'Indique la fecha tentativa.',
            'Fecha.after_or_equal' => 'La fecha tentativa no puede ser anterior a hoy.',
            'IdInventario.required' => 'Agregue al menos un producto.',
            'IdInventario.*.distinct' => 'Hay un producto repetido en la solicitud.',
            'Cantidad.*.min' => 'Cada cantidad debe ser de al menos 1.',
            'Cantidad.*.integer' => 'Las cantidades deben ser números enteros.',
            'Observaciones.max' => 'Las observaciones no pueden superar 500 caracteres.',
        ]);

        $ids = array_map('intval', $request->IdInventario);
        $cantidades = array_map('intval', $request->Cantidad);

        if (count($ids) !== count($cantidades)) {
            return back()->withInput()->withErrors(['general' => 'La solicitud está incompleta. Vuelva a agregar los productos.']);
        }

        // Solo productos de la división del usuario (el buscador ya filtra, el servidor también debe comprobarlo)
        $permitidos = DB::table('Inventario')
            ->whereIn('Id', $ids)
            ->where('IdDivisiones', $usuario->id_div)
            ->count();

        if ($permitidos !== count(array_unique($ids))) {
            return back()->withInput()->withErrors(['general' => 'Uno de los productos no está disponible para su división.']);
        }

        try {
            $idSolicitud = DB::transaction(function () use ($usuario, $request, $ids, $cantidades) {
                $solicitud = new solicitudPedidos();
                $solicitud->IdSolicita = $usuario->id;
                $solicitud->IdArea = $usuario->id_area;
                $solicitud->FechaTentativa = $request->Fecha;
                $solicitud->Observaciones = $request->Observaciones;
                $solicitud->Estado = 1;
                $solicitud->save();

                foreach ($ids as $i => $idInventario) {
                    $linea = new pedidoinventario();
                    $linea->IdInventario = $idInventario;
                    $linea->CantidadS = $cantidades[$i];
                    $linea->IdSolicitud = $solicitud->id;
                    $linea->save();
                }

                return $solicitud->id;
            });
        } catch (\Throwable $e) {
            Log::error('Error al guardar la solicitud de productos: ' . $e->getMessage());

            return back()->withInput()->withErrors(['general' => 'No se pudo registrar la solicitud. Intente de nuevo.']);
        }

        $aviso = $this->notificarPedido($idSolicitud, $usuario);

        return redirect('/homeInventario')
            ->with($aviso ? 'warning' : 'success', $aviso ?: 'Se envió la solicitud de pedido.');
    }

    /** Envía los correos del pedido. Devuelve un aviso si algo falló (el pedido ya está guardado). */
    private function notificarPedido(int $idSolicitud, Usuario $usuario): ?string
    {
        try {
            $pedidoinventario = DB::table('pedidoinventario')
                ->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')
                ->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')
                ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                ->join('empleados as E', 'E.Id', '=', 'S.IdSolicita')
                ->join('Areas as A', 'A.Id', '=', 'S.IdArea')
                ->select('S.Id', 'pedidoinventario.CantidadS as Cantidad', 'Pro.Nombre as NombreP', 'E.Nombre as NombreE', 'E.Apellidos', 'A.Nombre', 'S.FechaTentativa', 'S.Observaciones')
                ->where('pedidoinventario.IdSolicitud', '=', $idSolicitud)
                ->get();

            $Observacion = DB::table('solicitudpedidos')
                ->select('Observaciones')
                ->where('Id', '=', $idSolicitud)
                ->limit(1)
                ->get();

            $FechaT = DB::table('solicitudpedidos')
                ->select('FechaTentativa')
                ->where('Id', '=', $idSolicitud)
                ->get();

            $Area = Area::where('id', $usuario->id_area)->get();
            $Empleado = Usuario::where('id', $usuario->id)->get();

            foreach ($Empleado as $item) {
                if (!empty($item->Correo)) {
                    Mail::to($item->Correo)->send(new EnvioProd($pedidoinventario, $Empleado, $Area, $FechaT, $Observacion));
                }
            }

            $destinatarios = config('inventario.correos_pedidos', []);
            if ($destinatarios) {
                Mail::to($destinatarios)->send(new NotifyMail($pedidoinventario, $Empleado, $Area, $FechaT, $Observacion));
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Pedido ' . $idSolicitud . ' guardado, pero no se enviaron los correos: ' . $e->getMessage());

            return 'La solicitud se guardó, pero no se pudo enviar el correo de aviso.';
        }
    }
}
