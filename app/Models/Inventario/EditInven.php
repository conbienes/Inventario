<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditInven extends Model
{
    protected $table= 'AjusteInventario';
    protected $fillable=['Cantidades,Comentario,IdInventario,created_at,'];
    protected $guarded = ['Id'];
    
}
