<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table= 'categoria_inventario';
    protected $fillable=['Nombre,IdEmpleado,Estado'];
    protected $guarded = ['Id'];
    
}
