<?php

namespace App\Models\BonoRegalo;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'invoice_id',
        'method_id',
        'amount',
        'reference',
        'status',
    ];

    // Relación: pago pertenece a un método
    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }

    // Relación: pago pertenece a una factura
    public function invoice()
    {
        return $this->belongsTo(FacturaBonoR::class, 'invoice_id');
    }
}
