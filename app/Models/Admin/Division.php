<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $table= 'divisiones';
    protected $fillable=['nombre'];
    protected $guarded = ['id'];
}
