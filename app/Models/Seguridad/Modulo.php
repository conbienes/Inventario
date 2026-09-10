<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulos';
    protected $fillable = ['nombre'];

    // Relación simple Modulo ↔ Usuarios (empleado_modulo)
    public function usuariosSimples()
    {
        return $this->belongsToMany(Usuario::class, 'empleado_modulo', 'modulo_id', 'empleado_id');
    }

    // Relación avanzada Modulo ↔ Usuarios (empleado_modulo_permiso)
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'empleado_modulo_permiso', 'id_modulo', 'id_empleado')->withPivot('id_permiso');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'empleado_modulo_permiso', 'id_modulo', 'id_permiso')->using(EmpleadoModuloPermiso::class)->withPivot('id_empleado');
    }
}
