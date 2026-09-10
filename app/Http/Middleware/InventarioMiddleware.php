<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventarioMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

         if (session('modulo_seleccionado') != 1 || !Auth::user()->modulos()->where('modulo_id', 1)->exists()) {
            abort(403, 'Acceso no autorizado al módulo de Inventario');
        }
        return $next($request);
    }
}
