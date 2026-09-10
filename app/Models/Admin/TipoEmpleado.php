<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class TipoEmpleado extends Model
{
    protected $table='tipoempleados';
    protected $fillables=['nombre'];
    protected $guarded=['id'];
}
