<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'url', 'id_carpeta', 'id_inmClient', 'id_expInm', 'id_hisClient',
        'fecha', 'cod_documental', 'descripcion', 'etiquetas',
        'drive_item_id', 'web_url', 'download_url', 'size', 'file_type'
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];
}
