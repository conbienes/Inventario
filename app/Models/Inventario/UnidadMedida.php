<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table= 'unidad_medida';
    protected $fillable=['Nombre,Estado,Abreviatura,IdEmpleado,created_at'];
    protected $guarded = ['id'];
    
}
