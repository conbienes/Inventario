<?php

namespace App\Models\BonoRegalo;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seguridad\Usuario;

class FacturaBonoR extends Model
{
    protected $table = 'facturasBonoR';

    protected $fillable = [
        'numero_factura',
        'fecha',
        'cliente_id',
        'total',
        'usuario_id',
        'estado_bc',
        'bc_intentos',
        'bc_ultimo_error'
    ];

    public function cliente()
    {
        return $this->belongsTo(ClienteBonoR::class, 'cliente_id');
    }

    public function items()
    {
        return $this->hasMany(ItemFacturaBonoR::class, 'factura_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }
}

