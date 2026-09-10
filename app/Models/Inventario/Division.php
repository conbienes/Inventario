<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $table= 'divisiones';
    protected $fillable=['nombre'];
    protected $guarded = ['Id'];
    
}
