<?php

namespace App\Models\BonoRegalo;

use Illuminate\Database\Eloquent\Model;
class ItemFacturaBonoR extends Model
{
    protected $table = 'item_facturaBonoR';

    protected $fillable = [
        'factura_id',
        'tarjeta_id',
        'cantidad',
        'precio_unitario',
    ];

    public function factura()
    {
        return $this->belongsTo(FacturaBonoR::class, 'factura_id');
    }

    public function tarjeta()
    {
        return $this->belongsTo(TarjetaBonoR::class, 'tarjeta_id');
    }
}
