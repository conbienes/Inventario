<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table= 'regtickets';
    protected $fillable=['id_empleado','id_restaurante','id_estado','created_at'];
    protected $guarded = ['id'];
}
