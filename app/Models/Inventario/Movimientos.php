<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimientos extends Model
{
    protected $table= 'movimientos';
    protected $fillable=['Ingreso,Salida,Descripcion,IdTipoMovimiento,IdEmpleado,IdArea,IdSolicitante,IdInventario,
                        IdProducto,created_at'];
    protected $guarded = ['Id'];
    
}
