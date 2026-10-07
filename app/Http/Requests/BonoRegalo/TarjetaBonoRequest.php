<?php

namespace App\Http\Requests\BonoRegalo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Alta y edición manual de una tarjeta Bono Regalo. */
class TarjetaBonoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ruta: nivel:3,1|2|5. La regla "vendida no se edita" está en TarjetaBonoRPolicy
    }

    public function rules(): array
    {
        return [
            'numero' => ['required', 'digits:16', Rule::unique('tarjetasBonoR', 'numero')->ignore($this->route('id'))],
            'nit' => 'required|string|max:20',
            'valor' => 'required|numeric|min:1',
            'estado' => 'required|in:activa,inactiva',
        ];
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'El número de tarjeta es obligatorio.',
            'numero.digits' => 'El número de tarjeta debe tener exactamente 16 dígitos.',
            'numero.unique' => 'Este número de tarjeta ya está registrado.',
            'valor.numeric' => 'El valor debe ser un número válido.',
            'valor.min' => 'El valor debe ser mayor a 0.',
            'estado.in' => 'El estado debe ser "activa" o "inactiva".',
        ];
    }
}
