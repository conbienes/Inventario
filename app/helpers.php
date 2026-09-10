<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('tienePermisoModulo')) {
    /**
     * Verifica si un empleado tiene algún nivel (o un nivel específico) en un módulo.
     *
     * @param int        $empleadoId   ID del usuario autenticado (auth()->id())
     * @param int        $moduloId     1=Inventario, 2=Restaurante, 3=BonoRegalo
     * @param int[]|null $niveles      Ej: [1,2] para Admin o Admin2. Null = cualquiera.
     * @return bool
     */
    function tienePermisoModulo(int $empleadoId, int $moduloId, ?array $niveles = null): bool
    {
        $nivel = DB::table('empleado_modulo_permiso')
            ->where('id_empleado', $empleadoId)
            ->where('id_modulo', $moduloId)
            ->value('id_permiso');

        if (! $nivel) {
            return false;
        }

        // Si no se pide nivel específico, basta con que exista el registro
        if (is_null($niveles) || $niveles === []) {
            return true;
        }

        return in_array((int)$nivel, array_map('intval', $niveles), true);
    }
}
