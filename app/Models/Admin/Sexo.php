<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Sexo extends Model
{
    protected $table= 'sexos';
    protected $fillable=['nombre'];
    protected $guarded = ['id'];
}
