<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Informes extends Model
{
    protected $table= 'regtickets';
    protected $fillable=['id_empleado','id_restaurant'];
    protected $guarded = ['id'];
}
