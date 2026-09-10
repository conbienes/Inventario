<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckModuleAccess
{
     public function handle($request, Closure $next)
    {

        if (session('modulo_seleccionado') != 3 || !Auth::user()->modulos()->where('modulo_id', 3)->exists()) {
            abort(403, 'Acceso no autorizado al módulo de Bono Regalo');
        }

        return $next($request);
    }
}
