<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class solicitudPedidos extends Model
{
    protected $table= 'solicitudPedidos';
    protected $fillable=['Comentario,Estado,IdSolicita,IdAprueba,IdArea,Observaciones,FecAprobacion,FechaTentativa,created_at'];
    protected $guarded = ['Id'];    
}
