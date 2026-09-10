<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BonoRegalo\ClienteBonoR;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Services\BusinessCentralService;

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
    public function store(Request $request, BusinessCentralService $bc)
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

        // Nombre unificado (opcional, crea la columna si la usas)
        $nombreMostrar = $esEmpresa ? $UP($v['razon_social'] ?? null) : trim(implode(' ', array_filter([$UP($v['nombre'] ?? null), $UP($v['segundo_nombre'] ?? null), $UP($v['apellidos'] ?? null), $UP($v['segundo_apellido'] ?? null)])));

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

            $sync = $this->syncCustomerToBC($cliente, $bc, $nombreMostrar);

            $syncDimension = $this->crearDimension($cliente, $bc, $nombreMostrar);

            // 4) Marcar cargado y campos BC
            $cliente->cargado = $sync['ok'] ? 1 : 0;
            $cliente->bc_system_id = $sync['final']['systemId'] ?? null;
            $cliente->bc_etag = $sync['final']['@odata.etag'] ?? null;
            $cliente->bc_synced_at = now();
            $cliente->bc_error = $sync['ok'] ? null : $sync['error'] ?? null;
            $cliente->save();

            $msgDim = !empty($syncDimension['ok']) && $syncDimension['ok'] ? ' creada/actualizada.' : ' (dimensión pendiente: ' . ($syncDimension['error'] ?? 'error desconocido') . ')';

            return redirect()
                ->route('BonoRegalo.IndexFacturas')
                ->with('success', 'Cliente registrado. ' . ($cliente->cargado ? 'Sincronizado ' : 'Pendiente por BC') . $msgDim);
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['error' => 'Error al guardar el cliente en la base de datos: ' . $e->getMessage()])
                ->withInput();
        }
    }

    private function syncCustomerToBC(ClienteBonoR $c, BusinessCentralService $bc, ?string $nombreMostrar = null): array
    {
        try {
            $companyId = env('BC_COMPANY_ID');
            if (!$companyId) {
                return ['ok' => false, 'error' => 'Falta BC_COMPANY_ID'];
            }

            $esEmpresa = in_array($c->tipo_documento, ['NIT', 'NIT de otro país'], true);

            $tipoIdentificacion = $c->tipo_documento; // usa tus rótulos/códigos
            $tipoContribuyente = $esEmpresa ? 'Persona Jurídica' : 'Persona Natural';
            $areaImpuesto = $c->tipoActividad; // "CLI-NRI" / "CLI-RI"
            $correo = strtolower($c->correo);

            if (!$nombreMostrar) {
                $nombreMostrar = $esEmpresa ? $c->razons : trim(implode(' ', array_filter([$c->nombre, $c->segundo_nombre, $c->apellidos, $c->segundo_apellido])));
            }

            // CREATE (sin NumeroIdentificacion)
            $payloadCreate = array_filter(
                [
                    'No' => $c->cedula,
                    'NombreCompleto' => $nombreMostrar ?: null,
                    'Correo' => $correo ?: null,
                    'CorreoFE' => $correo ?: null,
                    'Tipoidentificacion' => $tipoIdentificacion ?: null,
                    'TipoContribuyente' => $tipoContribuyente,
                    'AreaImpuesto' => $areaImpuesto,
                    'PrimerNombre' => $esEmpresa ? null : ($c->nombre ?: null),
                    'SegundoNombre' => $esEmpresa ? null : ($c->segundo_nombre ?: null),
                    'PrimerApellido' => $esEmpresa ? null : ($c->apellidos ?: null),
                    'SegundoApellido' => $esEmpresa ? null : ($c->segundo_apellido ?: null),
                    'RazonSocial' => $esEmpresa ? ($c->razons ?: null) : null,
                ],
                fn($v) => !(is_string($v) && trim($v) === '') && $v !== null,
            );

            $created = $bc->createCustomerExt($companyId, $payloadCreate);

            $systemId = $created['systemId'] ?? ($created['SystemId'] ?? null);

            // PATCH (NumeroIdentificacion)
            $patched = null;
            $okCreate = (bool) $systemId;
            $okPatch = true;

            if ($okCreate && !empty($c->cedula)) {
                $patched = $bc->updateCustomerExt($companyId, $systemId, [
                    'NumeroIdentificacion' => (string) $c->cedula,
                ]);
            }
            // Tomamos el objeto “final” para persistir (si hubo PATCH, preferimos patched)
            $final = $patched ?: $created ?: [];

            // Notar que el JSON trae "@odata.etag" y "lastModified"
            $ok = $okCreate && ($patched !== null ? true : true); // si tu servicio lanza excepción, ya cae al catch

            return [
                'ok' => $ok,
                'final' => $final, // <-- lo usaremos para mapear y guardar
                'created' => $created,
                'patched' => $patched,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function crearDimension(ClienteBonoR $c, BusinessCentralService $bc, ?string $nombreMostrar = null): array
    {
        try {
            // Lee de .env (con defaults sensatos)
            $companyId = env('BC_COMPANY_ID');
            $dimensionCode = env('BC_DIM_TERCERO', 'TERCERO');

            if (!$companyId) {
                return ['ok' => false, 'error' => 'Falta BC_COMPANY_ID'];
            }

            // Code: cédula -> bc_no -> id local
            $code = (string) ($c->cedula ?: $c->bc_no ?: $c->id);

            // Name: si no viene, lo componemos desde el modelo
            if ($nombreMostrar === null || trim($nombreMostrar) === '') {
                $nombreMostrar = $c->razon_social ?: trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([$c->nombre, $c->segundo_nombre, $c->apellidos, $c->segundo_apellido]))));
            }

            // Recortes de seguridad (ajusta si tu API limita distinto)
            $payload = [
                'DimensionCode' => $dimensionCode, // <--- usa el de .env
                'Code' => mb_substr($code, 0, 50),
                'Name' => mb_substr((string) $nombreMostrar, 0, 100),
            ];

            // Limpia nulls/cadenas vacías
            $payload = array_filter($payload, fn($v) => !((is_string($v) && trim($v) === '') || $v === null));

            // UPSERT en BC
            $resp = $bc->upsertDimensionValue($companyId, $payload);

            // (Opcional) guardar algo en BD:
            // $c->update(['bc_dim_code'=>$payload['Code'],'bc_dim_name'=>$payload['Name'],'bc_dim_synced_at'=>now()]);

            return ['ok' => true, 'payload' => $payload, 'response' => $resp];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
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
            return back()
                ->withErrors(['error' => 'Error al actualizar el cliente: ' . $e->getMessage()])
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
