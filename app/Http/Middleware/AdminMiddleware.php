<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Asumiendo que id_tipoempleado == 1 es admin
        if (Auth::user()->id_tipoempleado != 1) {
            abort(403, 'Acceso solo para administradores');
        }

        return $next($request);
    }
}
