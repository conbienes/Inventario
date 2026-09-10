<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class EmpleadoModulo extends Model
{
    protected $table = 'empleado_modulo';
    public $timestamps = false;
    public $incrementing = false;   // PK compuesta
    protected $primaryKey = null;   // sin PK única
    protected $fillable = ['empleado_id', 'modulo_id'];
    protected $casts = [
        'empleado_id' => 'int',
        'modulo_id'   => 'int',
    ];
}