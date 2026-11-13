<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seguridad\Usuario;
use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\EmpleadoModuloPermiso;
use App\Models\Seguridad\EmpleadoModulo;
use App\Models\Seguridad\Permiso;
use App\Models\Admin\TipoEmpleado;
use App\Models\Admin\Area;
use App\Models\Admin\Division;
use App\Models\Admin\Sexo;
use App\Models\Admin\Estado;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ValidarUsuario;
use App\Http\Requests\contrasenaRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class RegEmpleadoController extends Controller
{
    public function __construct()
    {
        // Protege todo el controlador con auth.
        // (Si prefieres solo algunas rutas, deja solo en routes/web.php)
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (Auth::check()) {
            return view('regEmpleado.index', [
                'empleado' => Usuario::all(),
                'TipoEmpleado' => TipoEmpleado::all(),
                'sexo' => Sexo::all(),
                'area' => Area::all(),
                'division' => Division::all(),
                'estado' => Estado::all(),
                'modulos' => Modulo::all(),
                'permisos' => Permiso::all(),
                'catalogoEspeciales' => \App\Models\Seguridad\PermisoEspecial::CODIGOS,
            ]);
        } else {
            return redirect('/');
        }
    }

    public function consultar(Request $request)
    {
        if (Auth::check()) {
            $buscar = strtoupper(trim((string) $request->get('texto', '')));
            $orden = (string) $request->get('orden', '');

            $query = DB::table('empleados as e')
                ->leftJoin('areas as a', 'a.id', '=', 'e.id_area')
                ->leftJoin('Estados as s', 's.id', '=', 'e.id_estado')
                ->select('e.id', 'e.cedula', 'e.nombre', 'e.apellidos', 'e.Correo', 'a.nombre as area', 's.nombre as estado');

            if ($buscar !== '') {
                $query->where(function ($q) use ($buscar) {
                    $q->where('e.cedula', 'like', '%' . $buscar . '%')
                        ->orWhere('e.nombre', 'like', '%' . $buscar . '%')
                        ->orWhere('e.apellidos', 'like', '%' . $buscar . '%');
                });
            }

            // Filtros por lista: solo se aplican con un id numérico válido.
            foreach (['estado' => 'e.id_estado', 'division' => 'e.id_div', 'area' => 'e.id_area'] as $parametro => $columna) {
                $valor = (string) $request->get($parametro, '');
                if (ctype_digit($valor)) {
                    $query->where($columna, (int) $valor);
                }
            }

            if ($orden === 'nombre') {
                $query->orderBy('e.nombre')->orderBy('e.apellidos');
            } elseif ($orden === 'recientes') {
                $query->orderByDesc('e.created_at')->orderByDesc('e.id');
            } else {
                $query->orderBy('e.apellidos')->orderBy('e.nombre');
            }

            $empleado = $query->paginate(20)->withQueryString();

            // Opciones de los filtros
            $estados = DB::table('estados')->orderBy('nombre')->pluck('nombre', 'id')->all();
            $divisiones = DB::table('divisiones')->orderBy('nombre')->pluck('nombre', 'id')->all();
            $areas = DB::table('areas')->orderBy('nombre')->pluck('nombre', 'id')->all();

            // Módulos asignados a cada empleado de la página (menú: tabla empleado_modulo)
            $modulosPorEmpleado = DB::table('empleado_modulo as em')
                ->join('modulos as m', 'm.id', '=', 'em.modulo_id')
                ->whereIn('em.empleado_id', $empleado->pluck('id'))
                ->orderBy('m.nombre')
                ->get(['em.empleado_id', 'm.nombre'])
                ->groupBy('empleado_id');

            $empleado->getCollection()->transform(function ($fila) use ($modulosPorEmpleado) {
                $fila->modulos = ($modulosPorEmpleado[$fila->id] ?? collect())->pluck('nombre')->all();
                return $fila;
            });

            return view('regEmpleado.consulta', compact('empleado', 'estados', 'divisiones', 'areas'));
        } else {
            return redirect('/');
        }
    }

    public function CambiarEmpresa()
    {
        return view('regEmpleado.CambiarEmpresa', [
            'division' => Division::all(),
        ]);

        return redirect('/homeInventario')->with('success', 'El usuario ha sido registrado');
    }

    public function ActualizarEmpresa(Request $request)
    {
        try {
            // Validación de entrada
            $validator = Validator::make($request->all(), [
                'Empresa' => 'required|integer|exists:divisiones,id',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $empresaId = $request->input('Empresa');
            $empleado = Usuario::find(Auth::id());

            if (!$empleado) {
                return redirect()->back()->with('error', 'Usuario no encontrado.');
            }

            // Actualizar la división del empleado
            $empleado->id_div = $empresaId;
            $empleado->save();

            // Buscar el nombre de la división
            $division = Division::find($empresaId);
            if (!$division) {
                return redirect()->back()->with('error', 'La empresa seleccionada no existe.');
            }

            return redirect('/homeInventario')->with('success', 'Se cambió para la empresa ' . $division->nombre);
        } catch (QueryException $e) {
            // Error de base de datos
            Log::error('Error al actualizar empresa: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Hubo un problema al actualizar la empresa. Intenta de nuevo.');
        } catch (\Exception $e) {
            // Cualquier otro error inesperado
            Log::error('Excepción en ActualizarEmpresa: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Ocurrió un error inesperado. Contacte al administrador.');
        }
    }


    /** Módulos donde el usuario que hace el cambio es administrador (permiso 1 o 2). */
    private function modulosAdministrables(): array
    {
        return DB::table('empleado_modulo_permiso')
            ->where('id_empleado', Auth::id())
            ->whereIn('id_permiso', [1, 2])
            ->pluck('id_modulo')
            ->map(fn ($m) => (int) $m)
            ->unique()
            ->values()
            ->all();
    }

    /** Solo conserva los módulos que el administrador actual puede asignar. */
    private function filtrarModulos($modulos): array
    {
        if (!is_array($modulos)) {
            return [];
        }
        return array_intersect_key($modulos, array_flip($this->modulosAdministrables()));
    }

    public function guardar(ValidarUsuario $request)
    {
        $ruta = match (session('modulo_seleccionado')) {
            2 => route('InicioInventario'),
            3 => route('BonoRegalo.inicio'),
            default => route('InicioInventario'),
        };

        $request->merge(['modulos' => $this->filtrarModulos($request->input('modulos'))]);

        DB::transaction(function () use ($request) {
            // 1) Crear usuario
            $usuario = new Usuario();
            $usuario->cedula = $request->cedula;
            $usuario->nombre = $request->nombre;
            $usuario->apellidos = $request->apellidos;
            $usuario->Correo = $request->email; // o 'correo' si tu columna es en snake_case
            $usuario->id_sexo = $request->id_sexo;
            $usuario->id_estado = $request->id_estado;
            $usuario->id_tipoempleado = $request->id_tipoempleado;
            $usuario->password = Hash::make($request->password);
            $usuario->id_div = $request->id_div;
            $usuario->id_area = $request->id_area;
            $usuario->Documental = 0;
            $usuario->save();

            // Solo quien administra Inventario (módulo 1) puede asignar permisos especiales de Inventario
            if (in_array(1, $this->modulosAdministrables(), true)) {
                \App\Models\Seguridad\PermisoEspecial::sincronizar((int) $usuario->id, (array) $request->input('permisos_especiales', []));
            }

            // 2) Guardar permisos por módulo (tabla ternaria)
            if ($request->has('modulos') && is_array($request->modulos)) {

                // ---- Tabla ternaria: empleado_modulo_permiso (id_empleado/id_modulo/id_permiso)
                $rowsPermisos = [];
                foreach ($request->modulos as $id_modulo => $permisos) {
                    if (!is_array($permisos)) continue;
                    foreach ($permisos as $id_permiso) {
                        $rowsPermisos[] = [
                            'id_empleado' => $usuario->id,
                            'id_modulo'   => (int) $id_modulo,
                            'id_permiso'  => (int) $id_permiso,
                        ];
                    }
                }
                if ($rowsPermisos) {
                    // Requiere PK/índice único sobre (id_empleado,id_modulo,id_permiso)
                    DB::table('empleado_modulo_permiso')->upsert(
                        $rowsPermisos,
                        ['id_empleado', 'id_modulo', 'id_permiso'],
                        [] // sin updates en conflicto
                    );
                    // Si prefieres, puedes usar insert() si no te preocupan duplicados:
                    // DB::table('empleado_modulo_permiso')->insert($rowsPermisos);
                }

                // ---- Tabla pivot: empleado_modulo (empleado_id/modulo_id)
                $rowsModulos = collect(array_keys($request->modulos))
                    ->map(fn ($moduloId) => [
                        'empleado_id' => $usuario->id,
                        'modulo_id'   => (int) $moduloId,
                    ])
                    ->unique(fn ($r) => $r['empleado_id'].'-'.$r['modulo_id'])
                    ->values()
                    ->all();

                if ($rowsModulos) {
                    DB::table('empleado_modulo')->upsert(
                        $rowsModulos,
                        ['empleado_id', 'modulo_id'],
                        []
                    );
                }
            }
        });

        return redirect($ruta)->with('success', 'El usuario ha sido registrado correctamente.');
    }

    public function contrasena()
    {
        if (!Auth::check()) {
            return redirect('/')->withErrors(['auth' => 'Debe iniciar sesión para acceder.']);
        }

        try {
            $empleado = Usuario::findOrFail(auth()->user()->id);
            return view('regEmpleado.contrasena', compact('empleado'));
        } catch (ModelNotFoundException $e) {
            // Si el usuario autenticado no existe en la tabla empleados
            return redirect('/')->withErrors(['usuario' => 'El usuario no fue encontrado en el sistema.']);
        } catch (Exception $e) {
            // Cualquier otro error inesperado
            return redirect('/')->withErrors(['error' => 'Ha ocurrido un error inesperado. Inténtelo de nuevo más tarde.']);
        }
    }

    public function actcontrasena(contrasenaRequest $request, $id)
    {
        if (Auth::check()) {
            // Solo se cambia la contraseña propia. Un administrador cambia la de otros desde actualizar_empleado.
            abort_unless((int) $id === (int) Auth::id(), 403);

            $empleado = Usuario::findOrFail($id);
            $empleado->password = Hash::make($request['password']);
            $empleado->save();

            // Obtener el módulo seleccionado
            $modulo = session('modulo_seleccionado');

            // Redirección según el módulo
            switch ($modulo) {
                case 1:
                    return redirect('/homeInventario')->with('success', 'La contraseña se ha sido actualizada');
                case 2:
                    return redirect('/rutaModulo2')->with('success', 'La contraseña ha sido actualizada');
                case 3:
                    return redirect('/BonoRegalo/Inicio')->with('success', 'La contraseña ha sido actualizada');
                default:
                    return redirect('/homeInventario')->with('success', 'La contraseña ha sido actualizada');
            }
        } else {
            return redirect('/');
        }
    }

    public function editar($id)
    {
        if (!Auth::check()) {
            return redirect('/')->withErrors(['auth' => 'Debe iniciar sesión para acceder.']);
        }

        $empleado = Usuario::findOrFail($id);
        $TipoEmpleado = TipoEmpleado::all();
        $sexo = Sexo::all();
        $estado = Estado::all();
        $area = Area::all();
        $division = Division::all();
        $modulos = Modulo::all();
        $permisos = Permiso::all();

        // Obtener permisos actuales del empleado
        $permisosAsignados = EmpleadoModuloPermiso::where('id_empleado', $id)
            ->get()
            ->groupBy('id_modulo')
            ->map(function ($items) {
                return $items->pluck('id_permiso')->toArray();
            })
            ->toArray();

        $permisosEspeciales = \App\Models\Seguridad\PermisoEspecial::de((int) $empleado->id);
        $catalogoEspeciales = \App\Models\Seguridad\PermisoEspecial::CODIGOS;

        return view('regEmpleado.editar', compact('empleado', 'TipoEmpleado', 'sexo', 'estado', 'area', 'division', 'modulos', 'permisos', 'permisosAsignados', 'permisosEspeciales', 'catalogoEspeciales'));
    }

    public function actualizar(ValidarUsuario $request, $id)
    {
        // IMPORTANTE: quitar dd($request) en producción

        $request->merge(['modulos' => $this->filtrarModulos($request->input('modulos'))]);

        DB::transaction(function () use ($request, $id) {
            $empleado = Usuario::findOrFail($id);

            // Actualiza campos básicos
            $empleado->cedula = $request->cedula;
            $empleado->nombre = $request->nombre;
            $empleado->apellidos = $request->apellidos;
            $empleado->Correo = $request->email; // verifica nombre real de columna
            $empleado->id_sexo = $request->id_sexo;
            $empleado->id_estado = $request->id_estado;
            $empleado->id_tipoempleado = $request->id_tipoempleado;
            $empleado->id_div = $request->id_div;
            $empleado->id_area = $request->id_area;

            // Solo si viene password no vacío
            if ($request->filled('password')) {
                $empleado->password = Hash::make($request->password);
            }

            $empleado->save();

            // Solo quien administra Inventario (módulo 1) puede cambiar permisos especiales de Inventario
            if (in_array(1, $this->modulosAdministrables(), true)) {
                \App\Models\Seguridad\PermisoEspecial::sincronizar((int) $empleado->id, (array) $request->input('permisos_especiales', []));
            }

            // Reset de permisos del empleado
            // Solo se reemplazan los permisos de los módulos que administra quien hace el cambio;
            // los permisos de otros módulos se conservan intactos.
            DB::table('empleado_modulo_permiso')
                ->where('id_empleado', $empleado->id)
                ->whereIn('id_modulo', $this->modulosAdministrables())
                ->delete();

            // empleado_modulo alimenta el menú lateral y la relación modulos(): debe reflejar los cambios
            // (antes solo se escribía al crear el empleado).
            DB::table('empleado_modulo')
                ->where('empleado_id', $empleado->id)
                ->whereIn('modulo_id', $this->modulosAdministrables())
                ->delete();

            $filasModulos = [];
            foreach ((array) $request->input('modulos', []) as $moduloId => $permisos) {
                if (is_array($permisos) && count($permisos) > 0) {
                    $filasModulos[] = ['empleado_id' => $empleado->id, 'modulo_id' => (int) $moduloId];
                }
            }
            if ($filasModulos) {
                DB::table('empleado_modulo')->insert($filasModulos);
            }

            // Insert masivo de los nuevos permisos (si vienen)
            if ($request->has('modulos') && is_array($request->modulos)) {
                $rows = [];
                foreach ($request->modulos as $modulo_id => $permisos) {
                    if (!is_array($permisos)) {
                        continue;
                    }
                    foreach ($permisos as $permiso_id) {
                        $rows[] = [
                            'id_empleado' => $empleado->id,
                            'id_modulo' => (int) $modulo_id,
                            'id_permiso' => (int) $permiso_id,
                        ];
                    }
                }
                if (!empty($rows)) {
                    DB::table('empleado_modulo_permiso')->insert($rows);
                }
            }
        });

        return redirect()->route('consultar_Empleado')->with('success', 'El empleado ha sido actualizado');
    }
}
