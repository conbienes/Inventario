<?php

namespace App\Models\BonoRegalo;

use Illuminate\Database\Eloquent\Model;

class TarjetaBonoR extends Model
{
    protected $table = 'tarjetasBonoR';

    protected $fillable = [
        'numero',
        'nit',
        'valor',
        'estado',
    ];

    public function itemsFactura()
    {
        return $this->hasMany(ItemFacturaBonoR::class, 'tarjeta_id');
    }
}
