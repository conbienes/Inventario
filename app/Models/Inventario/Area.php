<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table= 'areas';
    protected $fillable=['nombre,created_at,'];
    protected $guarded = ['Id'];
    
}
