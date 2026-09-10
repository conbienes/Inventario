<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class registro_producto extends Model
{
    protected $table= 'registro_producto';
    protected $fillable=['PrecioTotal,IdProveedor,Nfactura,IdDivisiones,Estado,IdEmpleado,IdTipoMov,OrdenPedido,FechaIngreso,created_at'];
    protected $guarded = ['Id'];    
}
