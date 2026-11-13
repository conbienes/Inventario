<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class contrasenaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',        // al menos una mayúscula
                'regex:/[0-9]/',        // al menos un número
                'regex:/[^A-Za-z0-9]/', // al menos un carácter especial
            ],
        ];
    }

    public function messages()
    {
        return [
            'current_password.required' => 'Debe escribir su contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'Debe escribir la nueva contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación no coincide con la nueva contraseña.',
            'password.regex' => 'La contraseña debe incluir mayúscula, número y carácter especial.',
        ];
    }
}
