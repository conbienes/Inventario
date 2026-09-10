<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoMov extends Model
{
    protected $table= 'tipomovimiento';
    protected $fillable=['Nombre,created_at'];
    protected $guarded = ['id'];
    
}
