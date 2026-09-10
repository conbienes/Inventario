<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckModuloNivel
{
    /**
     * Usos soportados (retro-compatibles + extras):
     *   nivel:1                    // módulo 1, cualquier permiso, modo all
     *   nivel:1,1|2                // módulo 1, permisos 1 o 2 (any-perm), modo all
     *   nivel:1|2,1|2|6,any        // módulos 1 o 2, permisos 1/2/6, basta uno (any)
     *
     *   nivel:current,1|2|6        // PERM EN EL MÓDULO ACTUAL (session('modulo_seleccionado'))
     *   nivel:1|2,1|2|6,any,in:1|2 // además exige estar en módulo 1 o 2 actualmente
     *   nivel:3,1|2|6,all,eq:3     // exige estar en el módulo 3 actualmente
     *
     * Parámetros:
     *   0: módulos (ej: "1|2|3" o "current")
     *   1: permisos (ej: "1|2|6" o vacío/null = cualquiera)
     *   2: modo sobre módulos: "all" (por defecto) | "any"
     *   3: contexto de sesión: "in:1|2" | "notin:3" | "eq:3" | "neq:3" (opcional)
     */
    public function handle(Request $request, Closure $next, ...$params)
    {
        // 0) Auth
        if (!Auth::check()) {
            abort(403, 'No autenticado.');
        }
        $userId = Auth::id();

        // 1) Parseo parámetros
        $modulesArg = $params[0] ?? '';
        $permsArg   = $params[1] ?? null;
        $mode       = strtolower((string)($params[2] ?? 'all'));
        $ctx        = $params[3] ?? null;

        if (!in_array($mode, ['all', 'any'], true)) {
            $mode = 'all';
        }

        $toIntList = function (?string $s): array {
            if ($s === null || $s === '') return [];
            return array_values(array_filter(array_map(
                fn($v) => is_numeric($v = trim($v)) ? (int)$v : null,
                explode('|', $s)
            ), fn($v) => $v !== null));
        };

        // 2) Resolver módulos (soporta 'current')
        $current = (int) (session('modulo_seleccionado') ?? 0);
        if (preg_match('/\bcurrent\b/i', (string)$modulesArg)) {
            if ($current <= 0) abort(403, 'No hay un módulo actual seleccionado.');
            $moduloIds = [$current];
        } else {
            $moduloIds = $toIntList($modulesArg);
        }
        if (empty($moduloIds)) {
            abort(403, 'Config middleware inválida: sin módulos.');
        }

        // 3) Resolver permisos (any-of por defecto)
        $permitidos = $permsArg !== null ? $toIntList($permsArg) : [];

        // 4) Validación de CONTEXTO (módulo actual obligatorio/prohibido)
        if ($ctx) {
            [$kind, $rest] = array_pad(explode(':', (string)$ctx, 2), 2, '');
            $kind = strtolower(trim($kind));
            $vals = $toIntList($rest);

            if (in_array($kind, ['in', 'eq'], true)) {
                if (empty($vals)) abort(403, 'Config middleware inválida: contexto vacío.');
                $ok = $kind === 'in' ? in_array($current, $vals, true) : $current === $vals[0];
                if (!$ok) abort(403, 'Esta acción no está permitida en el módulo actual.');
            } elseif (in_array($kind, ['notin', 'neq'], true)) {
                $bad = $kind === 'notin' ? in_array($current, $vals, true) : $current === $vals[0];
                if ($bad) abort(403, 'Esta acción no se permite en este módulo.');
            }
        }

        // 5) Carga única de permisos del usuario (1 query)
        $rows = DB::table('empleado_modulo_permiso')
            ->select('id_modulo', 'id_permiso')
            ->where('id_empleado', $userId)
            ->whereIn('id_modulo', $moduloIds)
            ->get()
            ->groupBy('id_modulo')
            ->map(fn($grp) => $grp->pluck('id_permiso')->all()); // mapa: moduloId => [permisos...]

        // 6) Evaluación
        $cumplidos = 0;
        foreach ($moduloIds as $mId) {
            $permsDelModulo = $rows[$mId] ?? [];

            $okModulo = empty($permitidos)
                ? !empty($permsDelModulo) // cualquier permiso en ese módulo
                : (bool) array_intersect($permitidos, $permsDelModulo); // any-of

            if ($okModulo) {
                $cumplidos++;
                if ($mode === 'any') {
                    return $next($request);
                }
            } else {
                if ($mode === 'all') {
                    abort(403, "No tienes el nivel requerido en el módulo {$mId}.");
                }
            }
        }

        if ($mode === 'any' && $cumplidos === 0) {
            abort(403, 'No cumples el nivel requerido en ninguno de los módulos.');
        }

        return $next($request);
    }
}
