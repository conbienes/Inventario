<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table= 'Estados';
    protected $fillable=['nombre'];
    protected $guarded = ['id'];
}
