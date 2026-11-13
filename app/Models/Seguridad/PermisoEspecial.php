<?php

namespace App\Models\Seguridad;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Permisos especiales asignados a un empleado (tabla permisos_especiales).
 * Se consultan con PermisoEspecial::tiene('CODIGO') en lugar de comparar IDs fijos.
 */
class PermisoEspecial
{
    /** Códigos válidos y su descripción (para la ficha del empleado). */
    public const CODIGOS = [
        'APROBAR_PAPELERIA' => 'Aprueba solicitudes que incluyan papelería',
        'APROBAR_SOLICITUDES' => 'Aprueba cualquier solicitud de inventario',
    ];

    public static function tiene(string $codigo, ?int $empleadoId = null): bool
    {
        $empleadoId ??= Auth::id();
        if (!$empleadoId) {
            return false;
        }

        return DB::table('permisos_especiales')
            ->where('id_empleado', $empleadoId)
            ->where('codigo', $codigo)
            ->exists();
    }

    /** Códigos asignados a un empleado. */
    public static function de(int $empleadoId): array
    {
        return DB::table('permisos_especiales')
            ->where('id_empleado', $empleadoId)
            ->pluck('codigo')
            ->all();
    }

    /** Reemplaza los permisos especiales del empleado, ignorando códigos desconocidos. */
    public static function sincronizar(int $empleadoId, array $codigos): void
    {
        $validos = array_values(array_intersect(array_keys(self::CODIGOS), $codigos));

        DB::table('permisos_especiales')->where('id_empleado', $empleadoId)->delete();

        $filas = array_map(fn ($codigo) => [
            'id_empleado' => $empleadoId,
            'codigo' => $codigo,
            'created_at' => now(),
            'updated_at' => now(),
        ], $validos);

        if ($filas) {
            DB::table('permisos_especiales')->insert($filas);
        }
    }
}
