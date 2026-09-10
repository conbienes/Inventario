<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EmpleadoModuloPermiso extends Pivot
{
    protected $table = 'empleado_modulo_permiso';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['id_empleado', 'id_modulo', 'id_permiso'];
}
