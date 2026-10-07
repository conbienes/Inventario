<?php

namespace App\Http\Requests\BonoRegalo;

use Illuminate\Foundation\Http\FormRequest;

/** Registro de una venta (recibo de caja) de tarjetas Bono Regalo. */
class VentaBonoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el acceso lo controla la ruta (nivel:3,1|2|3)
    }

    public function rules(): array
    {
        return [
            'cliente' => 'required|integer|exists:clientesBonoR,id',
            'tarjetas' => 'required|array|min:1',
            'tarjetas.*' => 'integer|distinct|exists:tarjetasBonoR,id',
            'payments' => 'required|array|min:1',
            'payments.*.method_id' => 'required|integer|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.reference' => 'nullable|string|max:100',
            // 'fecha' y 'precios' llegan del formulario pero se ignoran:
            // la fecha es la del servidor y el precio el valor de la tarjeta en BD
        ];
    }

    public function messages(): array
    {
        return [
            'cliente.required' => 'Debe seleccionar un cliente.',
            'cliente.exists' => 'El cliente seleccionado no existe.',
            'tarjetas.required' => 'Debe seleccionar al menos una tarjeta.',
            'tarjetas.*.distinct' => 'Una tarjeta está repetida en la venta.',
            'tarjetas.*.exists' => 'Una de las tarjetas seleccionadas no existe.',
            'payments.required' => 'Debe registrar al menos un medio de pago.',
            'payments.*.amount.min' => 'El valor de cada pago debe ser mayor a 0.',
        ];
    }
}
