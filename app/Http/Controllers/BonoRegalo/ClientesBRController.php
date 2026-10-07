<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BonoRegalo\ClienteBonoR;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Jobs\BonoRegalo\SincronizarClienteBCJob;
use App\Services\BonoRegalo\ClienteBCSyncService;

class ClientesBRController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Verificar que el módulo seleccionado sea Bono Regalo (2)
            if (session('modulo_seleccionado') != 3) {
                abort(403, 'Acceso no autorizado para este módulo');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $tiposDocumento = ['Cédula de Ciudadanía', 'Registro Civil', 'Tarjeta de Identidad', 'Tarjeta de Extranjería', 'NIT', 'Pasaporte', 'NIT de otro país'];
        $tiposActividad = ['CLI-NRI', 'CLI-RI'];

        return view('BonoRegalo.Clientes.index', compact('tiposDocumento', 'tiposActividad'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function BuscarCliente(Request $request)
    {
        $search = $request->input('search');

        $clientes = ClienteBonoR::when($search, function ($query, $search) {
            return $query
                ->where('cedula', 'like', "%$search%")
                ->orWhere('nombre', 'like', "%$search%")
                ->orWhere('apellidos', 'like', "%$search%")
                ->orWhere('correo', 'like', "%$search%");
        })->paginate(15);

        return view('BonoRegalo.Clientes.BuscarCliente', compact('clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ClienteBCSyncService $syncService)
    {
        // Normalización
        $request->merge([
            'nombre' => trim($request->nombre ?? ''),
            'segundo_nombre' => trim($request->segundo_nombre ?? ''),
            'apellidos' => trim($request->apellidos ?? ''),
            'segundo_apellido' => trim($request->segundo_apellido ?? ''),
            'razon_social' => trim($request->razon_social ?? ''),
            'email' => strtolower(trim($request->email ?? '')),
            'cedula' => preg_replace('/\D+/', '', $request->cedula ?? ''), // solo dígitos
        ]);

        $esEmpresa = in_array($request->tipo_documento, ['NIT', 'NIT de otro país'], true);

        // (Opcional) Forzar coherencia de tipoActividad
        // if ($esEmpresa)  { $request->merge(['tipoActividad' => 'CLI-RI']); }
        // else             { $request->merge(['tipoActividad' => 'CLI-NRI']); }

        $rules = [
            'tipo_documento' => 'required|in:Cédula de Ciudadanía,Registro Civil,Tarjeta de Identidad,Tarjeta de Extranjería,NIT,Pasaporte,NIT de otro país',

            // Cedula/NIT: 9 dígitos para NIT, 8–10 para persona natural
            'cedula' => [
                'required',
                // usa regex para controlar longitud exacta y permitir ceros a la izquierda
                Rule::when(
                    fn () => in_array(request('tipo_documento'), ['NIT','NIT de otro país']),
                    ['regex:/^\d{9}$/'],           // NIT: exactamente 9 dígitos
                    ['regex:/^\d{6,10}$/']         // CC/otros: 8 a 10 dígitos
                ),
                Rule::unique('clientesBonoR', 'cedula'),
            ],

            'email' => ['required','email','max:255', Rule::unique('clientesBonoR','correo')],
            'tipoActividad' => 'required|in:CLI-NRI,CLI-RI',

            // --- Empresa (solo con NIT) ---
            'razon_social' => [
                'exclude_unless:tipo_documento,NIT,NIT de otro país',
                'required_if:tipo_documento,NIT,NIT de otro país',
                'string','max:255',
            ],

            // --- Persona natural (excluir si es NIT) ---
            // Al excluir, también los marcamos como nullable para que no molesten si llegan vacíos
            'nombre' => [
                'exclude_if:tipo_documento,NIT,NIT de otro país',
                'nullable','string','max:20',
            ],
            'apellidos' => [
                'exclude_if:tipo_documento,NIT,NIT de otro país',
                'nullable','string','max:20',
            ],
            'segundo_nombre' => [
                'exclude_if:tipo_documento,NIT,NIT de otro país',
                'nullable','string','max:20',
            ],
            'segundo_apellido' => [
                'exclude_if:tipo_documento,NIT,NIT de otro país',
                'nullable','string','max:20',
            ],
        ];

        $messages = [
            // --- Tipo de documento ---
            'tipo_documento.required' => 'El campo tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento seleccionado no es válido.',

            // --- Cédula / NIT ---
            'cedula.required' => 'El campo cédula es obligatorio.',
            'cedula.regex' => 'El número de documento no tiene una longitud válida.',
            'cedula.unique' => 'Ya existe un cliente con este número de documento.',
            'cedula.numeric' => 'El número de documento debe contener solo números.',

            // --- Email ---
            'email.required' => 'El campo correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.max' => 'El correo electrónico no debe exceder los 255 caracteres.',
            'email.unique' => 'Ya existe un cliente con este correo electrónico.',

            // --- Tipo de actividad ---
            'tipoActividad.required' => 'El campo tipo de actividad es obligatorio.',
            'tipoActividad.in' => 'El tipo de actividad seleccionado no es válido.',

            // --- Empresa (solo con NIT) ---
            'razon_social.required_if' => 'La razón social es obligatoria para documentos tipo NIT.',

            // --- Persona natural ---
            'nombre.required_unless' => 'El nombre es obligatorio para documentos diferentes a NIT.',
            'apellidos.required_unless' => 'El apellido es obligatorio para documentos diferentes a NIT.',
        ];


        $v = $request->validate($rules, $messages);

        // Helper para mayúsculas con tildes
        $UP = fn($x) => $x !== null && $x !== '' ? mb_strtoupper($x, 'UTF-8') : null;

        try {
            $cliente = ClienteBonoR::create([
                'tipo_documento' => $v['tipo_documento'],
                'cedula' => $v['cedula'],
                'correo' => $v['email'],
                'tipoActividad' => $v['tipoActividad'],
                'razons' => $esEmpresa ? $UP($v['razon_social'] ?? null) : null,
                'nombre' => !$esEmpresa ? $UP($v['nombre'] ?? null) : null,
                'segundo_nombre' => !$esEmpresa ? $UP($v['segundo_nombre'] ?? null) : null,
                'apellidos' => !$esEmpresa ? $UP($v['apellidos'] ?? null) : null,
                'segundo_apellido' => !$esEmpresa ? $UP($v['segundo_apellido'] ?? null) : null,
            ]);

            // Sincronización con BC: en cola si BC_ASYNC=true, si no en esta misma petición
            if (config('services.bc.async')) {
                SincronizarClienteBCJob::dispatch($cliente->id);
                $msgBC = 'Se sincronizará con BC en unos minutos.';
            } else {
                $sync = $syncService->sincronizar($cliente);
                $msgBC = $sync['ok'] ? 'Sincronizado con BC.' : 'Pendiente por BC (se puede reintentar desde Contabilidad › Clientes).';
            }

            return redirect()
                ->route('BonoRegalo.IndexFacturas')
                ->with('success', 'Cliente registrado. ' . $msgBC);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['error' => 'No se pudo guardar el cliente. Intenta de nuevo o contacta a soporte.'])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cliente = ClienteBonoR::findOrFail($id);

        $tiposDocumento = ['Cédula de Ciudadanía', 'Registro Civil', 'Tarjeta de Identidad', 'Tarjeta de Extranjería', 'NIT', 'Pasaporte', 'NIT de otro país'];
        $tiposActividad = ['CLI-NRI', 'CLI-RI'];

        return view('BonoRegalo.Clientes.editarCliente', compact('tiposDocumento', 'tiposActividad', 'cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cliente = ClienteBonoR::findOrFail($id);

        // NORMALIZACIÓN (igual que store)
        $request->merge([
            'nombre' => trim(preg_replace('/\s+/', ' ', $request->input('nombre', ''))),
            'segundo_nombre' => trim(preg_replace('/\s+/', ' ', $request->input('segundo_nombre', ''))),
            'apellidos' => trim(preg_replace('/\s+/', ' ', $request->input('apellidos', ''))),
            'segundo_apellido' => trim(preg_replace('/\s+/', ' ', $request->input('segundo_apellido', ''))),
            'email' => strtolower(trim($request->input('email', ''))),
            'cedula' => preg_replace('/\D+/', '', $request->input('cedula', '')),
            'razon_social' => trim(preg_replace('/\s+/', ' ', $request->input('razon_social', ''))),
        ]);


        $tiposDocumentoValidos = ['Cédula de Ciudadanía', 'Registro Civil', 'Tarjeta de Identidad', 'Tarjeta de Extranjería', 'NIT', 'Pasaporte', 'NIT de otro país'];

        $model = new ClienteBonoR();
        $table = $model->getTable();

        // Validación (idéntica a store; ajusta rangos si tus NIT llevan más dígitos)
        $validated = $request->validate(
            [
                'tipo_documento' => ['required', Rule::in($tiposDocumentoValidos)],
                'cedula' => ['required', 'numeric', 'digits_between:5,10', Rule::unique($table, 'cedula')->ignore($cliente->id)],
                // Si NO es NIT, estos son requeridos:
                'nombre' => ['required_unless:tipo_documento,NIT,NIT de otro país', 'string', 'max:20'],
                'segundo_nombre' => ['nullable', 'string', 'max:20'],
                'apellidos' => ['required_unless:tipo_documento,NIT,NIT de otro país', 'string', 'max:20'],
                'segundo_apellido' => ['nullable', 'string', 'max:20'],
                'email' => ['required', 'email', 'max:255', Rule::unique($table, 'correo')->ignore($cliente->id)],
                'tipoActividad' => ['required', Rule::in(['CLI-NRI', 'CLI-RI'])],
                // Si es NIT, razón social requerida:
                'razon_social' => ['required_if:tipo_documento,NIT,NIT de otro país', 'nullable', 'string', 'max:255'],
            ],
            [
                'tipo_documento.required' => 'El campo tipo de documento es obligatorio.',
                'tipo_documento.in' => 'El tipo de documento seleccionado no es válido.',

                'cedula.required' => 'El campo cédula es obligatorio.',
                'cedula.numeric' => 'La cédula debe contener solo números.',
                'cedula.digits_between' => 'La cédula debe tener entre 5 y 10 dígitos.',
                'cedula.unique' => 'Ya existe un cliente con esta cédula.',

                'nombre.required_unless' => 'El nombre es obligatorio para documentos diferentes a NIT.',
                'apellidos.required_unless' => 'El apellido es obligatorio para documentos diferentes a NIT.',

                'email.required' => 'El campo correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo electrónico no es válido.',
                'email.max' => 'El correo electrónico no debe exceder los 255 caracteres.',
                'email.unique' => 'Ya existe un cliente con este correo electrónico.',

                'tipoActividad.required' => 'El campo tipo de actividad es obligatorio.',
                'tipoActividad.in' => 'El tipo de actividad seleccionado no es válido.',

                'razon_social.required_if' => 'La razón social es obligatoria para NIT.',
            ],
        );

        // Mapear valores finales (evitar strings vacíos; usar null donde aplique)
        $isNit = in_array($validated['tipo_documento'], ['NIT', 'NIT de otro país']);

        $data = [
            'tipo_documento' => $validated['tipo_documento'],
            'cedula' => $validated['cedula'],
            'correo' => $validated['email'],
            'tipoActividad' => $validated['tipoActividad'],
            'razons' => $isNit ? $validated['razon_social'] ?? null : null, // si no es NIT, forzamos null
            'nombre' => $isNit ? null : (isset($validated['nombre']) ? mb_strtoupper($validated['nombre']) : null),
            'segundo_nombre' => $isNit ? null : (!empty($validated['segundo_nombre']) ? mb_strtoupper($validated['segundo_nombre']) : null),
            'apellidos' => $isNit ? null : (isset($validated['apellidos']) ? mb_strtoupper($validated['apellidos']) : null),
            'segundo_apellido' => $isNit ? null : (!empty($validated['segundo_apellido']) ? mb_strtoupper($validated['segundo_apellido']) : null),
        ];
        try {
            DB::transaction(function () use ($cliente, $data) {
                $cliente->fill($data);
                $cliente->save(); // guarda solo si hay cambios (dirty)
            });

            if (!$cliente->wasChanged()) {
                // No hubo cambios reales; ayuda para depurar
                return back()->with('success', 'No se detectaron cambios para actualizar.')->withInput();
            }

            return redirect()->route('BonoRegalo.BuscarCliente')->with('success', 'Cliente actualizado correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['error' => 'No se pudo actualizar el cliente. Intenta de nuevo o contacta a soporte.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
