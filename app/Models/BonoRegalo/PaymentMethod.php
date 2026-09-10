<?php

namespace App\Models\BonoRegalo;

use Illuminate\Database\Eloquent\Model;


class PaymentMethod extends Model
{
    // Nombre de la tabla
    protected $table = 'payment_methods';

    // Laravel no usa created_at/updated_at aquí
    public $timestamps = false;

    // Campos asignables masivamente
    protected $fillable = ['id', 'name'];

    // Relación: un método tiene muchos pagos
    public function payments()
    {
        return $this->hasMany(Payment::class, 'method_id');
    }
}
