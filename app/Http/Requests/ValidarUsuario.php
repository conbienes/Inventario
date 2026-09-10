<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidarUsuario extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        if ($this->route('id')) {
            return [
                'cedula' => 'required|integer|min:5|max:99999999999|unique:empleados,id,' . $this->route('id'),
                'nombre' => 'required|min:4|max:255|string',
                'apellidos' => 'required|min:4|max:255|string',
                'id_sexo' => 'required',
                'id_estado' => 'required',
                'id_tipoempleado' => 'required',
                'id_div' => 'required',
                'id_area' => 'required',
                'password' => 'nullable',
            ];
        } else {
            return [
                'cedula' => 'required|integer|min:5|max:99999999999|unique:empleados',
                'nombre' => 'required|min:4|max:255|string',
                'apellidos' => 'required|min:4|max:255|string',
                'id_sexo' => 'required',
                'id_estado' => 'required',
                'id_tipoempleado' => 'required',
                'id_div' => 'required',
                'id_area' => 'required',
                'password' => 'nullable|min:5',
            ];
        }
    }

    /**
     * Etiquetas en español para los campos
     */
    public function attributes()
    {
        return [
            'cedula' => 'cédula',
            'nombre' => 'nombre',
            'apellidos' => 'apellidos',
            'id_sexo' => 'sexo',
            'id_estado' => 'estado',
            'id_tipoempleado' => 'tipo de empleado',
            'id_div' => 'división',
            'id_area' => 'área',
            'password' => 'contraseña',
        ];
    }

    public function messages()
{
    return [
        'cedula.required' => 'El campo cédula es obligatorio.',
        'cedula.integer' => 'La cédula debe ser un número.',
        'cedula.min' => 'La cédula debe tener al menos :min dígitos.',
        'cedula.max' => 'La cédula no debe superar los :max dígitos.',
        'cedula.unique' => 'La cédula ya está registrada.',

        'nombre.required' => 'El campo nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos :min caracteres.',
        'nombre.max' => 'El nombre no debe superar los :max caracteres.',
        'nombre.string' => 'El nombre debe ser texto.',

        'apellidos.required' => 'El campo apellidos es obligatorio.',
        'apellidos.min' => 'Los apellidos deben tener al menos :min caracteres.',
        'apellidos.max' => 'Los apellidos no deben superar los :max caracteres.',
        'apellidos.string' => 'Los apellidos deben ser texto.',

        'id_sexo.required' => 'Debe seleccionar un sexo.',
        'id_estado.required' => 'Debe seleccionar un estado.',
        'id_tipoempleado.required' => 'Debe seleccionar un tipo de empleado.',
        'id_div.required' => 'Debe seleccionar una división.',
        'id_area.required' => 'Debe seleccionar un área.',

        'password.min' => 'La contraseña debe tener al menos :min caracteres.',
        'password.same' => 'Las contraseñas no coinciden.',
    ];
}

}
