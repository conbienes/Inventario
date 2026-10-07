<?php

namespace App\Http\Requests\BonoRegalo;

use Illuminate\Foundation\Http\FormRequest;

/** Archivo de importación masiva de tarjetas (CSV, XLSX o XLS, máx. 2 MB). */
class ImportarTarjetasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ruta: nivel:3,1|2|5
    }

    public function rules(): array
    {
        return [
            'archivo' => 'required|file|max:2048|mimes:csv,txt,xlsx,xls',
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Debes seleccionar un archivo para importar.',
            'archivo.max' => 'El archivo no debe superar los 2MB.',
            'archivo.mimes' => 'Solo se permiten archivos .csv, .xlsx o .xls.',
        ];
    }
}
