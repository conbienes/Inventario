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
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\ValidationException;
use App\Models\Seguridad\PermisoEspecial;

class HomeInventarioController extends Controller
{
    public function Inicio()
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect('/');
        }

        $user = auth()->user();
        $division = (int) $user->id_div;
        $resumen = new \App\Services\Inventario\ResumenInventario($division);

        // Pendientes por aprobar: solo para quien puede aprobar (misma condición que el menú)
        $puedeAprobar = tienePermisoModulo($user->id, 1, [1, 2]);

        // Cada persona ve su propio inicio. Solo quien aprueba ve el panorama de la división.
        $datos = [
            'puedeAprobar' => $puedeAprobar,
            'nombreDivision' => (string) DB::table('Divisiones')->where('Id', $division)->value('nombre'),
            'mi' => $resumen->miResumen((int) $user->id),
        ];

        if ($puedeAprobar) {
            $datos += [
                'indicadores' => $resumen->indicadores(),
                'meses' => $resumen->movimientosPorMes(),
                'reposicion' => $resumen->reposicion(),
                'solicitudes' => $resumen->solicitudesRecientes(),
                'entradas' => $resumen->entradasRecientes(),
                'pendientesAprobar' => DB::table('solicitudpedidos')->where('Estado', 1)->count(),
            ];
        }

        return view('Inicio.inicioInventario', $datos);
    }

    public function Pendientes(Request $request)
    {
        if (Auth::check()) {
            [$desde, $hasta] = $this->rangoFechas($request, true);
            $orden = $this->orden($request, ['recientes', 'antiguas']);

            $query = DB::table('pedidoinventario')
                ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                ->select('pedidoinventario.IdSolicitud as Id', 'S.Estado', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'))
                ->where('S.IdSolicita', '=', auth()->user()->id)
                ->where('S.Estado', '=', 1)
                ->groupBy('pedidoinventario.IdSolicitud', 'Dia', 'S.Estado');

            $this->aplicarRango($query, $desde, $hasta);
            $this->aplicarOrdenFecha($query, $orden);

            $pedidoinventario = $query->paginate(20)->withQueryString();

            return view('Inventario.SolicProduc.SolicPendientes', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }

    public function vista($id)
    {
        if (Auth::check()) {
            $id = $this->idDesdeUrl($id);

            $pedidoinventario = DB::table('pedidoinventario')->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')->join('empleados as E', 'E.Id', '=', 'S.IdSolicita')->join('Areas as A', 'A.Id', '=', 'S.IdArea')->select('S.Id as IdSolicitud', 'pedidoinventario.CantidadS as Cantidad', DB::raw('DATE(S.created_at) as Fecha'), 'S.FechaTentativa', 'S.Observaciones', 'Pro.Nombre as NombreP', 'E.Nombre as NombreE', 'E.Apellidos as ApellidosE', 'A.Nombre as AreaS', 'S.Estado')->where('pedidoinventario.IdSolicitud', '=', $id)->get();

            if ($pedidoinventario->isEmpty()) {
                abort(404);
            }

            return view('Inventario.SolicProduc.VerSolicitudes', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }

    public function VistaAprobadas($id)
    {
        if (Auth::check()) {
            $id = $this->idDesdeUrl($id);

            $pedidoinventario = DB::table('pedidoinventario')->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')->join('empleados as E', 'E.Id', '=', 'S.IdSolicita')->leftJoin('empleados as Aprueba', 'Aprueba.Id', '=', 'S.IdAprueba')->join('Areas as A', 'A.Id', '=', 'S.IdArea')->select('pedidoinventario.Comentario', 'pedidoinventario.CantidadAp as CantAprobadas', 'S.Id as IdSolicitud', 'pedidoinventario.CantidadS as Cantidad', DB::raw('DATE(S.FecAprobacion) as Fecha'), 'Pro.Nombre as NombreProduc', 'E.Nombre as NombreEmp', 'E.Apellidos as ApellidoEmp', 'A.Nombre as AreaS', 'Aprueba.Nombre as NombreAprueba', 'Aprueba.Apellidos as ApellidosAprueba', 'S.Estado')->where('pedidoinventario.IdSolicitud', '=', $id)->get();

            if ($pedidoinventario->isEmpty()) {
                abort(404);
            }

            return view('Inventario.SolicProduc.SolicitudesAprobadasEmpleado', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }

    public function Solicitudes(Request $request)
    {
        if (Auth::check()) {
            $buscar = trim((string) $request->get('texto'));
            [$desde, $hasta] = $this->rangoFechas($request);
            $orden = $this->orden($request, ['recientes', 'antiguas', 'tentativa']);
            $area = $request->get('area');

            $query = DB::table('pedidoinventario')
                ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                ->leftJoin('empleados as E', 'E.Id', '=', 'S.IdSolicita')
                ->select('E.Nombre as NombreS', 'S.FechaTentativa as fecha', 'E.Apellidos as ApellidosS', 'S.Id as Id', 'S.Estado', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'))
                ->where('S.Estado', '=', 1)
                ->groupBy('E.Nombre', 'S.Id', 'Dia', 'S.Estado', 'E.Apellidos', 'S.FechaTentativa');

            $this->aplicarTextoSolicitud($query, $buscar);
            $this->aplicarRango($query, $desde, $hasta);

            if (ctype_digit((string) $area)) {
                $query->where('S.IdArea', (int) $area);
            }

            if ($orden === 'tentativa') {
                $query->orderBy('fecha')->orderBy('Id');
            } else {
                $this->aplicarOrdenFecha($query, $orden);
            }

            $pedidoinventario = $query->paginate(20)->withQueryString();
            $areas = DB::table('Areas')->orderBy('nombre')->pluck('nombre', 'id')->all();

            return view('Inventario.SolicProduc.SolicPorAprobar', compact('pedidoinventario', 'areas'));
        } else {
            return redirect('/');
        }
    }

    public function Cumplir($id)
    {
        if (Auth::check()) {
            $id = $this->idDesdeUrl($id);

            $pedidoinventario = DB::table('pedidoinventario')->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')->join('empleados as E', 'E.Id', '=', 'S.IdSolicita')->join('Areas as A', 'A.Id', '=', 'S.IdArea')->select('pedidoinventario.CantidadAp as CantAprobadas', 'S.Id as IdSolicitud', 'pedidoinventario.CantidadS as Cantidad', 'Pro.Nombre as NombreProduc', 'E.Nombre as NombreEmp', 'E.Apellidos as ApellidoEmp', 'A.Nombre as AreaS', 'I.Cantidades As Cantidades', 'S.Estado', 'S.FechaTentativa', 'S.Observaciones', 'pedidoinventario.Id as IdPedido', 'pedidoinventario.IdInventario', 'E.Id as IdEmpleado', 'A.Id AS IdArea')->where('pedidoinventario.IdSolicitud', '=', $id)->orderBy('NombreProduc')->get();

            if ($pedidoinventario->isEmpty()) {
                abort(404);
            }

            return view('Inventario.SolicProduc.VerSolicitadosAprobados', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }

    /** Categoría de inventario considerada papelería (permiso APROBAR_PAPELERIA). */
    public const CATEGORIA_PAPELERIA = 17;

    /**
     * Aprueba un pedido pendiente: fija la cantidad aprobada por línea, descuenta el stock,
     * registra el movimiento y marca la solicitud como aprobada (Estado 2).
     *
     * Reglas de permiso (se comprueban en el servidor, no solo en la vista):
     *  - APROBAR_SOLICITUDES: aprueba cualquier pedido.
     *  - APROBAR_PAPELERIA: solo aprueba pedidos que incluyan papelería.
     *  - Sin ninguno de los dos: no aprueba nada.
     *
     * Todo el cambio va en una transacción con filas bloqueadas. El correo se envía después
     * del commit; si falla, el pedido sigue aprobado y se avisa al usuario.
     */
    public function AprobarSolicitud(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $puedeTodo = PermisoEspecial::tiene('APROBAR_SOLICITUDES');
        $puedePapeleria = PermisoEspecial::tiene('APROBAR_PAPELERIA');

        if (!$puedeTodo && !$puedePapeleria) {
            return $this->sinPermiso();
        }

        $datos = $request->validate([
            'IdSolicitud' => ['required', 'integer'],
            'IdPedido' => ['required', 'array', 'min:1'],
            'IdPedido.*' => ['required', 'integer', 'min:1'],
            'IdInventario' => ['required', 'array'],
            'IdInventario.*' => ['required', 'integer', 'min:1'],
            'Catidad' => ['required', 'array'],
            'Catidad.*' => ['required', 'integer', 'min:0'],
            'Comentario' => ['nullable', 'array'],
            'Comentario.*' => ['nullable', 'string', 'max:200'],
        ]);

        $idSolicitud = (int) $datos['IdSolicitud'];
        $idsPedido = array_map('intval', $datos['IdPedido']);
        $idsInventario = array_map('intval', $datos['IdInventario']);
        $cantidades = array_map('intval', $datos['Catidad']);
        $comentarios = $datos['Comentario'] ?? [];
        $n = count($idsPedido);

        if (count($idsInventario) !== $n || count($cantidades) !== $n || count(array_unique($idsPedido)) !== $n) {
            throw ValidationException::withMessages(['general' => 'Los datos del pedido no coinciden. Recargue la página e inténtelo de nuevo.']);
        }

        $idAprobador = (int) Auth::id();

        $resultado = DB::transaction(function () use ($idSolicitud, $idsPedido, $idsInventario, $cantidades, $comentarios, $n, $puedeTodo, $puedePapeleria, $idAprobador) {
            $solicitud = DB::table('solicitudPedidos')->where('Id', $idSolicitud)->lockForUpdate()->first();

            if (!$solicitud) {
                throw ValidationException::withMessages(['general' => 'La solicitud no existe.']);
            }
            if ((int) $solicitud->Estado !== 1) {
                throw ValidationException::withMessages(['general' => 'La solicitud ya no está pendiente de aprobación.']);
            }

            $lineasBD = DB::table('pedidoinventario as P')
                ->join('Inventario as I', 'I.Id', '=', 'P.IdInventario')
                ->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')
                ->where('P.IdSolicitud', $idSolicitud)
                ->select('P.Id as IdLinea', 'P.IdInventario', 'P.CantidadS', 'I.IdCategoria', 'I.Cantidades', 'Pro.Nombre as NombreP')
                ->lockForUpdate()
                ->get()
                ->keyBy('IdLinea');

            if ($lineasBD->count() !== $n) {
                throw ValidationException::withMessages(['general' => 'La solicitud debe aprobarse completa: incluya todas sus líneas.']);
            }

            $tienePapeleria = false;
            $necesario = [];
            $existencia = [];

            foreach ($idsPedido as $i => $idPedido) {
                $linea = $lineasBD->get($idPedido);

                if (!$linea || (int) $linea->IdInventario !== $idsInventario[$i]) {
                    throw ValidationException::withMessages(['general' => 'Una línea no pertenece a esta solicitud.']);
                }
                if ($cantidades[$i] > (int) $linea->CantidadS) {
                    throw ValidationException::withMessages(['general' => "La cantidad aprobada de {$linea->NombreP} no puede ser mayor a la solicitada ({$linea->CantidadS})."]);
                }

                if ((int) $linea->IdCategoria === self::CATEGORIA_PAPELERIA) {
                    $tienePapeleria = true;
                }

                $necesario[$idsInventario[$i]] = ($necesario[$idsInventario[$i]] ?? 0) + $cantidades[$i];
                $existencia[$idsInventario[$i]] = (int) $linea->Cantidades;
            }

            if (!$puedeTodo && !($puedePapeleria && $tienePapeleria)) {
                return ['denegado' => true];
            }

            foreach ($necesario as $idInventario => $cantidad) {
                if ($cantidad > $existencia[$idInventario]) {
                    $nombre = $lineasBD->firstWhere('IdInventario', $idInventario)->NombreP ?? 'el producto';
                    throw ValidationException::withMessages(['general' => "Existencia insuficiente de {$nombre}: disponible {$existencia[$idInventario]}, se intentó aprobar {$cantidad}."]);
                }
            }

            foreach ($idsPedido as $i => $idPedido) {
                $cantidad = $cantidades[$i];

                DB::table('pedidoinventario')
                    ->where('Id', $idPedido)
                    ->update(['CantidadAp' => $cantidad, 'Comentario' => strtoupper((string) ($comentarios[$i] ?? ''))]);

                if ($cantidad > 0) {
                    $movimiento = new Movimientos();
                    $movimiento->Cantidad = $cantidad;
                    $movimiento->IdTipoMovimiento = 1;
                    $movimiento->Descripcion = strtoupper((string) ($comentarios[$i] ?? ''));
                    $movimiento->IdEmpleado = $idAprobador;
                    $movimiento->IdArea = $solicitud->IdArea;
                    $movimiento->IdSolicitante = $solicitud->IdSolicita;
                    $movimiento->IdInventario = $idsInventario[$i];
                    $movimiento->save();

                    DB::table('Inventario')->where('Id', $idsInventario[$i])->decrement('Cantidades', $cantidad);
                }
            }

            DB::table('solicitudPedidos')
                ->where('Id', $idSolicitud)
                ->update(['IdAprueba' => $idAprobador, 'FecAprobacion' => now(), 'Estado' => 2]);

            return ['denegado' => false, 'idSolicita' => (int) $solicitud->IdSolicita];
        });

        if ($resultado['denegado']) {
            return $this->sinPermiso();
        }

        $solicitante = Empleado::where('id', $resultado['idSolicita'])->select('nombre', 'apellidos', 'Correo')->first();
        Session::flash('success', 'La solicitud fue aprobada al empleado ' . ($solicitante->nombre ?? '') . ' ' . ($solicitante->apellidos ?? '') . ' ');

        $aviso = $this->notificarAprobacion($idSolicitud, $solicitante?->Correo);
        if ($aviso !== null) {
            Session::flash('warning', $aviso);
        }

        return Redirect::to(route('Solicitudes'));
    }

    private function sinPermiso()
    {
        Session::flash('success1', 'No tiene permisos, solicite permisos al administrador');

        return Redirect::to(route('Solicitudes'));
    }

    /**
     * Envía el correo de aprobación al solicitante. Devuelve null si se envió,
     * o un texto de aviso si no había correo o el envío falló (el pedido ya está aprobado).
     */
    private function notificarAprobacion(int $idSolicitud, ?string $correoSolicitante): ?string
    {
        if (empty($correoSolicitante)) {
            return 'La solicitud quedó aprobada, pero el solicitante no tiene correo registrado; no se envió notificación.';
        }

        try {
            $Correo = DB::table('pedidoinventario')
                ->join('Inventario as I', 'I.Id', '=', 'pedidoinventario.IdInventario')
                ->join('Producto as Pro', 'Pro.Id', '=', 'I.IdProducto')
                ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                ->join('empleados as E', 'E.Id', '=', 'S.IdAprueba')
                ->join('Areas as A', 'A.Id', '=', 'S.IdArea')
                ->select('S.IdAprueba', 'pedidoinventario.Comentario', 'S.IdSolicita', 'pedidoinventario.CantidadAp as CantidadA', 'pedidoinventario.CantidadS as CantidadS', 'Pro.Nombre as NombreP', 'E.Nombre as NombreE', 'E.Apellidos', 'E.Correo as Correo', 'A.Nombre')
                ->where('pedidoinventario.IdSolicitud', '=', $idSolicitud)
                ->get();

            $idAprueba = (int) ($Correo->first()->IdAprueba ?? 0);
            $Empleado = Empleado::where('id', $idAprueba)->select('nombre', 'apellidos', 'id_div')->get();

            Mail::to($correoSolicitante)->send(new ProdAprob($Correo, $Empleado));

            return null;
        } catch (\Throwable $e) {
            report($e);

            return 'La solicitud quedó aprobada, pero no se pudo enviar el correo al solicitante.';
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

            // Filtros nuevos de la barra (desde/hasta/empleado/texto/orden). Si llega alguno, tienen prioridad
            // sobre los del formulario "Filtro" (FechaInicial/FechaFinal/Empleados) y sobre los guardados en sesión.
            $usaBarra = $request->hasAny(['desde', 'hasta', 'empleado', 'texto', 'orden']);
            [$desde, $hasta] = $this->rangoFechas($request);

            // Validar los datos del request utilizando Laravel Validation
            $validated = $request->validate([
                'FechaInicial' => 'nullable|date',
                'FechaFinal' => 'nullable|date|after_or_equal:FechaInicial',
                'Empleados' => 'nullable|exists:empleados,id', // Validar que el empleado existe en la tabla 'empleados'
                'empleado' => 'nullable|integer',
                'texto' => 'nullable|string|max:100',
            ]);

            if ($usaBarra) {
                // La barra reemplaza al filtro guardado: así "Quitar todos" deja la lista completa.
                session()->forget(['FechaInicial', 'FechaFinal', 'Empleados']);
                $FechaI = $desde;
                $FechaF = $hasta;
                $Empleados = $request->input('empleado');
            } else {
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
            }

            // Solo solicitudes ya aprobadas (el contador del inicio también cuenta Estado 2)
            $query = DB::table('pedidoinventario')->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')->join('empleados as E', 'E.id', '=', 'S.IdSolicita')->where('S.Estado', '=', 2)->select('pedidoinventario.IdSolicitud as Id', 'S.Estado', 'E.nombre as Empleado', 'E.apellidos as Apellido', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'));

            // Aplicar filtros si existen
            if ($usaBarra) {
                $this->aplicarRango($query, $FechaI, $FechaF);
                $this->aplicarTextoSolicitud($query, trim((string) $request->input('texto')));
            } elseif ($FechaI && $FechaF) {
                $query->whereBetween(DB::raw('DATE(S.created_at)'), [$FechaI, $FechaF]);
            }

            if ($Empleados) {
                $query->where('E.id', $Empleados);
            }

            $this->aplicarOrdenFecha($query, $this->orden($request, ['recientes', 'antiguas']));

            // Agrupar y paginar resultados
            $pedidoinventario = $query->groupBy('pedidoinventario.IdSolicitud', 'Dia', 'S.Estado', 'Empleado', 'Apellido')->paginate(20)->withQueryString();

            // Empleados con al menos una solicitud aprobada (opciones del filtro)
            $empleados = DB::table('solicitudpedidos as S')
                ->join('empleados as E', 'E.id', '=', 'S.IdSolicita')
                ->where('S.Estado', 2)
                ->select('E.id', 'E.nombre', 'E.apellidos')
                ->distinct()
                ->orderBy('E.nombre')
                ->orderBy('E.apellidos')
                ->get()
                ->mapWithKeys(fn ($e) => [$e->id => trim($e->nombre . ' ' . $e->apellidos)])
                ->all();

            return view('Inventario.SolicProduc.SolicitudesAprobadas', compact('pedidoinventario', 'empleados'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $th) {
            report($th);

            return response()->json(
                [
                    'error' => 'Se ha producido un error. Por favor, inténtelo de nuevo más tarde.',
                ],
                500,
            );
        }
    }

    /**
     * Lee el rango desde/hasta (Y-m-d) del request. Las fechas inválidas se descartan y, si vienen
     * al revés, se intercambian. Los valores limpios se devuelven al request para que la barra de
     * filtros y la paginación muestren lo que realmente se aplicó.
     * Con $aceptarDate, el parámetro antiguo "date" (un solo día) equivale a desde = hasta = date.
     */
    private function rangoFechas(Request $request, bool $aceptarDate = false): array
    {
        $limpiar = function ($valor) {
            $valor = trim((string) $valor);
            $fecha = \DateTime::createFromFormat('!Y-m-d', $valor);

            return ($fecha && $fecha->format('Y-m-d') === $valor) ? $valor : null;
        };

        $desde = $limpiar($request->input('desde'));
        $hasta = $limpiar($request->input('hasta'));

        if ($aceptarDate && $request->has('date')) {
            $dia = $limpiar($request->input('date'));
            if ($dia && !$desde && !$hasta) {
                $desde = $hasta = $dia;
            }
            $request->query->remove('date');
            $request->request->remove('date');
        }

        if ($desde && $hasta && $desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $request->merge(['desde' => $desde, 'hasta' => $hasta]);

        return [$desde, $hasta];
    }

    /** Devuelve la opción de orden pedida si es válida; si no, la primera (por defecto). */
    private function orden(Request $request, array $validas): string
    {
        $orden = (string) $request->input('orden', '');

        return in_array($orden, $validas, true) ? $orden : $validas[0];
    }

    private function aplicarRango($query, ?string $desde, ?string $hasta): void
    {
        if ($desde) {
            $query->where(DB::raw('DATE(S.created_at)'), '>=', $desde);
        }
        if ($hasta) {
            $query->where(DB::raw('DATE(S.created_at)'), '<=', $hasta);
        }
    }

    /** Ordena por fecha de solicitud (alias Dia) y luego por número de solicitud (alias Id). */
    private function aplicarOrdenFecha($query, string $orden): void
    {
        $dir = $orden === 'antiguas' ? 'asc' : 'desc';
        $query->orderBy('Dia', $dir)->orderBy('Id', $dir);
    }

    /** Texto libre: número de solicitud exacto o nombre/apellidos del solicitante (alias E). */
    private function aplicarTextoSolicitud($query, string $texto): void
    {
        if ($texto === '') {
            return;
        }

        $like = '%' . $texto . '%';
        $query->where(function ($q) use ($texto, $like) {
            if (ctype_digit($texto)) {
                $q->orWhere('S.Id', (int) $texto);
            }
            $q->orWhere('E.nombre', 'like', $like)
                ->orWhere('E.apellidos', 'like', $like)
                ->orWhere(DB::raw("CONCAT(E.nombre, ' ', E.apellidos)"), 'like', $like);
        });
    }

    /** Descifra el ID de la URL; si no es válido responde 404 (no 500). */
    private function idDesdeUrl(string $cifrado): int
    {
        try {
            $id = Crypt::decrypt($cifrado);
        } catch (DecryptException $e) {
            abort(404);
        }

        if (!is_numeric($id) || (int) $id <= 0) {
            abort(404);
        }

        return (int) $id;
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
            [$desde, $hasta] = $this->rangoFechas($request, true);
            $orden = $this->orden($request, ['recientes', 'antiguas']);

            $query = DB::table('pedidoinventario')
                ->join('solicitudpedidos as S', 'S.Id', '=', 'pedidoinventario.IdSolicitud')
                ->join('empleados as E', 'E.id', '=', 'S.IdSolicita')
                ->select('pedidoinventario.IdSolicitud as Id', 'E.nombre as Empleado', 'E.apellidos as Apellido', 'S.Estado', DB::raw('DATE(S.created_at) as Dia'), DB::raw('COUNT(pedidoinventario.IdSolicitud) as Cantidad'))
                ->where('S.IdSolicita', '=', auth()->user()->id)
                ->where('S.Estado', '=', 2)
                ->groupBy('pedidoinventario.IdSolicitud', 'Dia', 'S.Estado', 'Empleado', 'Apellido');

            $this->aplicarRango($query, $desde, $hasta);
            $this->aplicarOrdenFecha($query, $orden);

            $pedidoinventario = $query->paginate(20)->withQueryString();

            return view('Inventario.SolicProduc.AprobadasEmpleado', compact('pedidoinventario'));
        } else {
            return redirect('/');
        }
    }
}
