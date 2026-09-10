<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table= 'Inventario';
    protected $fillable=['Cantidades,CantidadMin,Estado,CantidadMax,IdProducto,IdEmpleado,IdDivisiones,created_at,IdAlmacen,IdCategoria,Estanteria,Entrepano,Gaveta'];
    protected $guarded = ['Id'];
    
}
