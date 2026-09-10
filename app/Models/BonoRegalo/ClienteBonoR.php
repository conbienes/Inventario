<?php

namespace App\Models\BonoRegalo;

use Illuminate\Database\Eloquent\Model;

class ClienteBonoR extends Model
{
    protected $table = 'clientesBonoR';

    protected $fillable = ['tipo_documento', 'cedula', 'nombre', 'apellidos', 'correo', 'tipoActividad', 'segundo_nombre', 'segundo_apellido','razons','cargado', 'bc_system_id', 'bc_etag', 'bc_synced_at', 'bc_error'];


    public function facturas()
    {
        return $this->hasMany(FacturaBonoR::class, 'cliente_id');
    }
}
