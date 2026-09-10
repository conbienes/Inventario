<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpInmueble extends Model
{
    use HasFactory;

    protected $table = 'ExpInmueble'; // Nombre de la tabla en la BD

    protected $primaryKey = 'id'; // Clave primaria

    public $timestamps = false; // No usa `created_at` ni `updated_at`

    protected $fillable = [
        'idExpInmueble',
        'NOMENCLATURA',
        'NUMERODEUNIDADDOCUMENTAL',
        'piso',
        'Destinaciondelinmueble',
        'ACTIVO',
        'NRODELOCALES',
        'Tipoinm',
        'MILookupId',
        'Areatotalconstruida',
        'Areamezanine',
        'Areamesas',
        'Otrasareas',
        'Propietario',
        'Direccion',
        'Ubicacion_PH',
        'Municipio',
        'Admon',
        'Valor_M2',
        'Fecha_admon',
        'Lote'
    ];
}
