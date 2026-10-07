<?php

namespace App\Http\Requests\BonoRegalo;

use App\Models\BonoRegalo\ClienteBonoR;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta y edición de clientes Bono Regalo (mismas reglas en ambos casos).
 * En edición la ruta trae {id} y se ignora el propio registro en las reglas unique.
 */
class ClienteBonoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el acceso lo controla la ruta (nivel:3,1|2|3)
    }

    public function esEmpresa(): bool
    {
        return in_array($this->input('tipo_documento'), config('bono_regalo.documentos_empresa'), true);
    }

    protected function prepareForValidation(): void
    {
        $limpiar = fn($v) => trim(preg_replace('/\s+/', ' ', (string) $v));

        $this->merge([
            'nombre' => $limpiar($this->input('nombre')),
            'segundo_nombre' => $limpiar($this->input('segundo_nombre')),
            'apellidos' => $limpiar($this->input('apellidos')),
            'segundo_apellido' => $limpiar($this->input('segundo_apellido')),
            'razon_social' => $limpiar($this->input('razon_social')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'cedula' => preg_replace('/\D+/', '', (string) $this->input('cedula')), // solo dígitos
        ]);
    }

    public function rules(): array
    {
        $tabla = (new ClienteBonoR())->getTable();
        $id = $this->route('id'); // null en alta
        $empresa = implode(',', config('bono_regalo.documentos_empresa'));

        return [
            'tipo_documento' => ['required', Rule::in(config('bono_regalo.tipos_documento'))],
            // NIT: 9 dígitos (sin dígito de verificación). Personas: 6 a 10 dígitos
            'cedula' => [
                'required',
                $this->esEmpresa() ? 'regex:/^\d{9}$/' : 'regex:/^\d{6,10}$/',
                Rule::unique($tabla, 'cedula')->ignore($id),
            ],
            'email' => ['required', 'email', 'max:255', Rule::unique($tabla, 'correo')->ignore($id)],
            'tipoActividad' => ['required', Rule::in(config('bono_regalo.tipos_actividad'))],

            // Empresa
            'razon_social' => ["exclude_unless:tipo_documento,{$empresa}", 'required', 'string', 'max:255'],

            // Persona natural
            'nombre' => ["exclude_if:tipo_documento,{$empresa}", 'required', 'string', 'max:100'],
            'segundo_nombre' => ["exclude_if:tipo_documento,{$empresa}", 'nullable', 'string', 'max:100'],
            'apellidos' => ["exclude_if:tipo_documento,{$empresa}", 'required', 'string', 'max:100'],
            'segundo_apellido' => ["exclude_if:tipo_documento,{$empresa}", 'nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_documento.required' => 'El campo tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento seleccionado no es válido.',
            'cedula.required' => 'El número de documento es obligatorio.',
            'cedula.regex' => $this->esEmpresa()
                ? 'El NIT debe tener exactamente 9 dígitos (sin dígito de verificación).'
                : 'El número de documento debe tener entre 6 y 10 dígitos.',
            'cedula.unique' => 'Ya existe un cliente con este número de documento.',
            'email.required' => 'El campo correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.max' => 'El correo electrónico no debe exceder los 255 caracteres.',
            'email.unique' => 'Ya existe un cliente con este correo electrónico.',
            'tipoActividad.required' => 'El campo tipo de actividad es obligatorio.',
            'tipoActividad.in' => 'El tipo de actividad seleccionado no es válido.',
            'razon_social.required' => 'La razón social es obligatoria para documentos tipo NIT.',
            'nombre.required' => 'El nombre es obligatorio para documentos diferentes a NIT.',
            'apellidos.required' => 'El apellido es obligatorio para documentos diferentes a NIT.',
        ];
    }

    /** Datos listos para guardar en clientesBonoR (mayúsculas, null donde no aplica) */
    public function datosCliente(): array
    {
        $v = $this->validated();
        $mayus = fn($x) => ($x ?? '') === '' ? null : mb_strtoupper($x, 'UTF-8');
        $empresa = $this->esEmpresa();

        return [
            'tipo_documento' => $v['tipo_documento'],
            'cedula' => $v['cedula'],
            'correo' => $v['email'],
            'tipoActividad' => $v['tipoActividad'],
            'razons' => $empresa ? $mayus($v['razon_social'] ?? null) : null,
            'nombre' => $empresa ? null : $mayus($v['nombre'] ?? null),
            'segundo_nombre' => $empresa ? null : $mayus($v['segundo_nombre'] ?? null),
            'apellidos' => $empresa ? null : $mayus($v['apellidos'] ?? null),
            'segundo_apellido' => $empresa ? null : $mayus($v['segundo_apellido'] ?? null),
        ];
    }
}
