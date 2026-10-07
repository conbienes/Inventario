<?php

namespace App\Http\Requests\BonoRegalo;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Filtros de listados, reportes y exportaciones del módulo.
 * Evita errores 500 por fechas u ordenamientos inválidos en la URL (Carbon::parse, orderBy).
 */
class FiltrosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Cada pantalla usa su propio par de nombres de fecha
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'direction' => 'nullable|in:asc,desc,ASC,DESC',
            'sort' => 'nullable|string|max:30',
            'w' => 'nullable|integer|between:48,120', // ancho de impresión térmica en mm
        ];
    }

    public function messages(): array
    {
        return [
            '*.date' => 'La fecha no es válida.',
            'direction.in' => 'El orden debe ser ascendente o descendente.',
            'w.between' => 'El ancho de impresión debe estar entre 48 y 120 mm.',
            'to.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
            'fecha_fin.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ];
    }
}
