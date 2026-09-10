<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Restaurante extends Model
{
    protected $table= 'restaurantes';
    protected $fillable=['nit','nombre','id_estado'];
    protected $guarded = ['id'];
}
