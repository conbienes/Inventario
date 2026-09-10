<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';
    protected $fillable = ['nombre', 'descripcion'];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'empleado_modulo_permiso', 'id_permiso', 'id_empleado')->using(EmpleadoModuloPermiso::class)->withPivot('id_modulo');
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'empleado_modulo_permiso', 'id_permiso', 'id_modulo')->using(EmpleadoModuloPermiso::class)->withPivot('id_empleado');
    }
}
