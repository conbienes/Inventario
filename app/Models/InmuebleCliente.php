<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InmuebleCliente extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'inmuebles_clientes';

    protected $fillable = ['sharepoint_id', 'content_type', 'modified', 'created', 'cliente_id', 'inmueble_id', 'nit_cliente', 'numero_local', 'id_inmueble_cliente', 'categoria', 'marca', 'estado_inmueble', 'codigo_bc', 'cartera_pendiente', 'fecha_inicio_contrato', 'fecha_final_contrato', 'nomenclatura_bc', 'centro_costos_bc', 'tipo_garantia', 'asegurado_canon', 'asegurado_admon', 'tipo_canon', 'valor_canon', 'fecha_inicio', 'fecha_renovacion', 'fecha_final', 'renovaciones', 'incremento', 'comentario_canon'];

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'id_inmClient', 'sharepoint_id');
    }
}
