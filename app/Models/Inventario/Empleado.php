<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\Modulo;

class Empleado extends Model
{
    protected $table = 'empleados';
    protected $fillable = ['cedula', 'nombre', 'apellidos'];
    protected $guarded = ['id'];

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'empleado_modulo', 'empleado_id', 'modulo_id');
    }
}
