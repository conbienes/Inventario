<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\Modulo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'empleados';
    protected $fillable = ['cedula', 'nombre', 'apellidos'];
    protected $guarded = ['id'];

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'empleado_modulo', 'empleado_id', 'modulo_id');
    }
}
