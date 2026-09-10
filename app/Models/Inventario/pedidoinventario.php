<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pedidoinventario extends Model
{
    protected $table= 'pedidoinventario';
    protected $fillable=['CantidadS,CantidadAp,IdInventario,IdSolicitud,created_at'];
    protected $guarded = ['id'];
    
}
