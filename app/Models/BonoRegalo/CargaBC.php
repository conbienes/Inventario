<?php

namespace App\Models\BonoRegalo;

use Illuminate\Database\Eloquent\Model;

class CargaBC extends Model
{
    // Nombre de la tabla
    protected $table = 'CargaBC';

    // Laravel usará automáticamente 'id' como clave primaria, pero lo definimos por claridad
    protected $primaryKey = 'id';

    // Si la tabla tiene timestamps (created_at y updated_at)
    public $timestamps = true;

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'nombre',
        'apellidos',
        'cedula',
        'correo',
        'factura',
        'tarjeta',
        'fecha',
        'cargadoBC',
        'valor_tarjeta',
        'valor_total',
        'idEmpleado',
        'tipoActividad',
        'tipo_documento',
        'segundo_nombre',
        'segundo_apellido',
    ];

    // Si quieres que Laravel trate 'cargadoBC' como boolean y 'fecha' como date
    protected $casts = [
        'cargadoBC' => 'boolean',
        'fecha' => 'date',
        'valor_tarjeta' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];
}
