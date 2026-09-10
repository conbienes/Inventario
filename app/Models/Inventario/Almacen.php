<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table= 'Almacen';
    protected $fillable=['Nombre,Estado,IdEmpleado,created_at'];
    protected $guarded = ['Id'];
    
}
