<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ubic_topografica extends Model
{
    protected $table= 'ubic_topografica';
    protected $fillable=['Nombre,Estado,IdEmpleado,created_at'];
    protected $guarded = ['Id'];
    
}
