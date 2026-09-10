<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    // Por si Laravel necesita un fallback
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function index(Request $request)
    {
        if (Auth::check()) {
            // Si ya está logueado, redirige según módulo guardado

            return redirect($this->resolveModuleHome(Auth::user()->modulo_actual));
        }

        return view('Seguridad.login');
    }

    /**
     * Se ejecuta DESPUÉS de un login exitoso
     */
    protected function authenticated(Request $request, $user)
    {
        // Validar y normalizar módulo
        $modulo = (int) $request->input('modulo');
        if (!in_array($modulo, [1, 2, 3], true)) {
            $modulo = 1; // valor por defecto o lanza validation error
        }

        // Guardar selección
        $request->session()->put('modulo_seleccionado', $modulo);

        // Persistir en el usuario (requiere columna modulo_actual en users)
        $user->modulo_actual = $modulo;
        $user->save();

        // Redirigir según módulo
        return redirect($this->resolveModuleHome($modulo));
    }

    /**
     * SOLO credenciales reales para el guard
     */
    protected function credentials(Request $request)
    {
        return [
            'cedula'   => $request->cedula,
            'password' => $request->password,
        ];
    }

    public function username()
    {
        return 'cedula';
    }

    /**
     * Si Laravel llama a redirectTo (remember me, etc.),
     * decide destino según el módulo en sesión/usuario.
     */
    protected function redirectTo()
    {
        $modulo = session('modulo_seleccionado')
            ?? optional(Auth::user())->modulo_actual
            ?? 1;

        return $this->resolveModuleHome((int) $modulo);
    }

    /**
     * Centraliza las rutas por módulo.
     */
    private function resolveModuleHome(int $modulo): string
    {
        switch ($modulo) {
            case 1: // Inventario
                return route('Inicio');
            case 2: // Restaurante
                return route('BonoRegalo.Inicio.index');
            case 3: // Bono Regalo
                return route('BonoRegalo.inicio');
            default:
                return route('home');
        }
    }
}
