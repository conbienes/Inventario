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

if (! function_exists('divisionesInventario')) {
    /**
     * Divisiones (empresas) disponibles en el módulo Inventario. Se consulta una sola vez por petición.
     */
    function divisionesInventario(): \Illuminate\Support\Collection
    {
        static $divisiones = null;

        return $divisiones ??= DB::table('divisiones')->orderBy('id')->get(['id', 'nombre']);
    }
}

if (! function_exists('divisionInventarioActual')) {
    /**
     * División con la que el usuario está trabajando en Inventario (entradas, arqueo, alertas, existencias).
     * Si pasa $id, la recuerda en sesión. Si no, devuelve la última usada, la del usuario o la primera.
     */
    function divisionInventarioActual(?int $id = null): ?int
    {
        if ($id !== null) {
            session(['inv_division' => $id]);

            return $id;
        }

        $ids = divisionesInventario()->pluck('id')->map(fn ($v) => (int) $v)->all();

        foreach ([session('inv_division'), optional(auth()->user())->id_div] as $candidata) {
            if ($candidata !== null && in_array((int) $candidata, $ids, true)) {
                return (int) $candidata;
            }
        }

        return $ids[0] ?? null;
    }
}
