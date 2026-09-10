<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureModuloSeleccionado
{
    public function handle(Request $request, Closure $next, $modulosCsv)
    {
        $actual = (int) session('modulo_seleccionado');
        $permitidos = array_map('intval', explode('|', (string) $modulosCsv));

        if (!in_array($actual, $permitidos, true)) {
            abort(403, 'Esta acción solo está disponible en los módulos: '.implode(',', $permitidos).'.');
        }
        return $next($request);
    }
}
