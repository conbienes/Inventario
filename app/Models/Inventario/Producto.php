<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table= 'Producto';
    protected $fillable=['Nombre,Codigo,Estado,IdEmpleado,IdUnidadMedida,created_at'];
    protected $guarded = ['Id'];
    
}
