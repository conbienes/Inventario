<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table= 'areas';
    protected $fillable=['nombre'];
    protected $guarded = ['id'];
}
