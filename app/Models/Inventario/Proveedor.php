<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table= 'Proveedor';
    protected $fillable=['Nombre,Nit,IdEmpleado,Correo,Telefono,created_at'];
    protected $guarded = ['id'];
    
}
