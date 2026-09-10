@extends("theme.$theme.layout")

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">{{ __('Editar Cliente') }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-check"></i> Éxito</h5>
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Error en el formulario</h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="clienteForm" action="{{ route('BonoRegalo.actualizarCliente', $cliente->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <div class="row">
                                <!-- Columna Izquierda -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipo_documento">
                                            {{ __('Tipo de Documento') }} <span class="text-danger">*</span>
                                        </label>
                                        <select name="tipo_documento" id="tipo_documento" class="form-control select2" required>
                                            <option value="">{{ __('Seleccione...') }}</option>
                                            @foreach ($tiposDocumento as $tipo)
                                                <option value="{{ $tipo }}"
                                                    {{ old('tipo_documento', $cliente->tipo_documento) == $tipo ? 'selected' : '' }}>
                                                    {{ $tipo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="cedula">
                                            {{ __('Número de Documento') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="cedula" id="cedula"
                                            value="{{ old('cedula', $cliente->cedula) }}" required autocomplete="off"
                                            placeholder="{{ __('Ingrese el número de documento') }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="tipoActividad">
                                            {{ __('Tipo de Actividad') }} <span class="text-danger">*</span>
                                        </label>
                                        <select name="tipoActividad" id="tipoActividad" class="form-control select2" required>
                                            <option value="">{{ __('Seleccione...') }}</option>
                                            @foreach ($tiposActividad as $actividad)
                                                <option value="{{ $actividad }}"
                                                    {{ old('tipoActividad', $cliente->tipoActividad) == $actividad ? 'selected' : '' }}>
                                                    {{ $actividad === 'CLI-NRI' ? 'Persona Natural' : 'Persona Jurídica'  }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Columna Derecha -->
                                <div class="col-md-6">
                                    @php
                                        $docActual = old('tipo_documento', $cliente->tipo_documento);
                                        $esNit = in_array($docActual, ['NIT','NIT de otro país']);
                                    @endphp

                                    {{-- EMPRESA --}}
                                    <div id="empresaFields"
                                         style="display: {{ $esNit ? 'block' : 'none' }}; background:#e8f5e8; padding:10px; border-radius:5px;">
                                        <h5 style="color:green; margin-bottom:15px;">{{ __('Datos de Empresa') }}</h5>
                                        <div class="form-group">
                                            <label for="razon_social">{{ __('Razón Social') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="razon_social" id="razon_social"
                                                   value="{{ old('razon_social', $cliente->razons ?? '') }}"
                                                   autocomplete="off" placeholder="{{ __('Ingrese la razón social') }}">
                                        </div>
                                    </div>

                                    {{-- PERSONA NATURAL --}}
                                    <div id="personaFields"
                                         style="display: {{ $esNit ? 'none' : 'block' }}; background:#fff3cd; padding:10px; border-radius:5px;">
                                        <h5 style="color:orange; margin-bottom:15px;">{{ __('Datos Personales') }}</h5>

                                        <div class="form-group">
                                            <label for="nombre">{{ __('Primer Nombre') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nombre" id="nombre"
                                                   value="{{ old('nombre', $cliente->nombre) }}" autocomplete="off"
                                                   placeholder="{{ __('Ingrese el primer nombre') }}">
                                        </div>

                                        <div class="form-group">
                                            <label for="segundo_nombre">{{ __('Segundo Nombre') }}</label>
                                            <input type="text" class="form-control" name="segundo_nombre" id="segundo_nombre"
                                                   value="{{ old('segundo_nombre', $cliente->segundo_nombre) }}" autocomplete="off"
                                                   placeholder="{{ __('Ingrese el segundo nombre') }}">
                                        </div>

                                        <div class="form-group">
                                            <label for="apellidos">{{ __('Primer Apellido') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="apellidos" id="apellidos"
                                                   value="{{ old('apellidos', $cliente->apellidos) }}" autocomplete="off"
                                                   placeholder="{{ __('Ingrese el primer apellido') }}">
                                        </div>

                                        <div class="form-group">
                                            <label for="segundo_apellido">{{ __('Segundo Apellido') }}</label>
                                            <input type="text" class="form-control" name="segundo_apellido" id="segundo_apellido"
                                                   value="{{ old('segundo_apellido', $cliente->segundo_apellido) }}" autocomplete="off"
                                                   placeholder="{{ __('Ingrese el segundo apellido') }}">
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-top: 15px;">
                                        <label for="email">{{ __('Correo Electrónico') }} <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" id="email"
                                               value="{{ old('email', $cliente->correo ?? $cliente->email) }}"
                                               autocomplete="off" required placeholder="{{ __('ejemplo@dominio.com') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-save"></i> {{ __('Actualizar Cliente') }}
                            </button>
                            <a href="{{ route('BonoRegalo.BuscarCliente') }}" class="btn btn-danger">
                                <i class="fas fa-times"></i> {{ __('Cancelar') }}
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select2
            if (window.$ && $('.select2').length) {
                $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            }

            const tipoDocumentoSelect = document.getElementById('tipo_documento');
            const empresaFields       = document.getElementById('empresaFields');
            const personaFields       = document.getElementById('personaFields');

            function toggleFields() {
                const v = (tipoDocumentoSelect?.value || '').trim();
                const isNit = (v === 'NIT' || v === 'NIT de otro país');
                if (empresaFields && personaFields) {
                    empresaFields.style.display = isNit ? 'block' : 'none';
                    personaFields.style.display = isNit ? 'none'  : 'block';
                }

                // Requeridos condicionales (solo en cliente)
                const requiredEmpresa = ['razon_social'];
                const requiredPersona = ['nombre','apellidos'];

                requiredEmpresa.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.toggleAttribute('required', isNit);
                });
                requiredPersona.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.toggleAttribute('required', !isNit);
                });
            }

            // Inicial y on change (incluye Select2)
            toggleFields();
            if (tipoDocumentoSelect) {
                tipoDocumentoSelect.addEventListener('change', toggleFields);
                if (window.$) {
                    $('#tipo_documento').on('change', toggleFields);
                }
            }

            // Loader + protección doble submit
            const form = document.getElementById('clienteForm');
            if (form) {
                const submitBtn = form.querySelector('button[type="submit"]');
                form.addEventListener('submit', function (e) {
                    if (typeof form.checkValidity === 'function' && !form.checkValidity()) return;
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Actualizando...';
                    }
                    Swal.fire({
                        title: 'Actualizando cliente...',
                        html: '<div style="font-size:0.95em; color:#666;">Sincronizando</div>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => Swal.showLoading()
                    });
                });
            }
        });
    </script>
@endpush
