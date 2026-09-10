<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class itemfactura extends Model
{
    protected $table= 'itemfactura';
    protected $fillable=['Comentario,CantidadS,CantidadA,IdInventario,IdRegProduc,Completas,created_at,ValorUnidad'];
    protected $guarded = ['Id'];
    
}
