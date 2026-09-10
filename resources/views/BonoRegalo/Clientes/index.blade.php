@extends("theme.$theme.layout")

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">{{ __('Registro de Nuevo Cliente') }}</h3>
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

                    <form method="POST" action="{{ route('BonoRegalo.crearCliente') }}" id="clienteForm">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <!-- Columna Izquierda -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipo_documento">{{ __('Tipo de Documento') }} <span
                                                class="text-danger">*</span></label>
                                        <select name="tipo_documento" id="tipo_documento" class="form-control" required>
                                            <option value="">{{ __('Seleccione...') }}</option>
                                            @foreach ($tiposDocumento as $tipo)
                                                <option value="{{ $tipo }}"
                                                    {{ old('tipo_documento') == $tipo ? 'selected' : '' }}>
                                                    {{ $tipo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="tipoActividad">{{ __('Tipo') }} <span
                                                class="text-danger">*</span></label>
                                        <select name="tipoActividad" id="tipoActividad" class="form-control" required>
                                            <option value="">{{ __('Seleccione...') }}</option>
                                            @foreach ($tiposActividad as $actividad)
                                                <option value="{{ $actividad }}"
                                                    {{ old('tipoActividad') == $actividad ? 'selected' : '' }}>
                                                    {{ $actividad === 'CLI-NRI' ? 'Persona Natural' : 'Persona Jurídica' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin-top: 15px;">
                                        <label for="email">{{ __('Correo Electrónico') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" id="email"
                                            value="{{ old('email') }}" required autocomplete="off"
                                            placeholder="{{ __('ejemplo@dominio.com') }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="cedula">{{ __('Número de Documento') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="cedula" id="cedula"
                                            value="{{ old('cedula') }}" required autocomplete="off"
                                            placeholder="{{ __('Ingrese el número de documento') }}">
                                    </div>
                                </div>

                                <!-- Columna Derecha -->
                                <div class="col-md-6">
                                    {{-- BLOQUE: EMPRESA (solo Razón Social) --}}
                                    <div id="empresaFields"
                                        style="display: none; background: #e8f5e8; padding: 10px; border-radius: 5px;">
                                        <h5 style="color: green; margin-bottom: 15px;">{{ __('Datos de Empresa') }}</h5>
                                        <div class="form-group">
                                            <label for="razon_social">{{ __('Razón Social') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="razon_social" id="razon_social"
                                                value="{{ old('razon_social') }}" autocomplete="off"
                                                placeholder="{{ __('Ingrese la razón social') }}">
                                        </div>
                                    </div>

                                    {{-- BLOQUE: PERSONA NATURAL (nombres y apellidos) --}}
                                    <div id="personaFields" style="background: #fff3cd; padding: 10px; border-radius: 5px;">
                                        <h5 style="color: orange; margin-bottom: 15px;">{{ __('Datos Personales') }}</h5>
                                        <div class="form-group">
                                            <label for="nombre">{{ __('Primer Nombre') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nombre" id="nombre"
                                                value="{{ old('nombre') }}" autocomplete="off"
                                                placeholder="{{ __('Ingrese el primer nombre') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="segundo_nombre">{{ __('Segundo Nombre') }}</label>
                                            <input type="text" class="form-control" name="segundo_nombre"
                                                id="segundo_nombre" value="{{ old('segundo_nombre') }}"
                                                autocomplete="off" placeholder="{{ __('Ingrese el segundo nombre') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="apellidos">{{ __('Primer Apellido') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="apellidos" id="apellidos"
                                                value="{{ old('apellidos') }}" autocomplete="off"
                                                placeholder="{{ __('Ingrese el primer apellido') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="segundo_apellido">{{ __('Segundo Apellido') }}</label>
                                            <input type="text" class="form-control" name="segundo_apellido"
                                                id="segundo_apellido" value="{{ old('segundo_apellido') }}"
                                                autocomplete="off" placeholder="{{ __('Ingrese el segundo apellido') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Guardar Factura
                            </button>

                            <a href="{{ route('BonoRegalo.inicio') }}" class="btn btn-danger">
                                <i class="fas fa-times"></i> {{ __('Cancelar') }}
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    {{-- JavaScript INLINE para asegurar que funcione --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('JavaScript cargado correctamente!');

            const tipoDocumentoSelect = document.getElementById('tipo_documento');
            const empresaFields = document.getElementById('empresaFields');
            const personaFields = document.getElementById('personaFields');

            console.log('Elementos encontrados:', {
                tipoDocumentoSelect: !!tipoDocumentoSelect,
                empresaFields: !!empresaFields,
                personaFields: !!personaFields
            });

            function toggleFields() {
                console.log('Cambio detectado. Valor:', tipoDocumentoSelect.value);

                const isNit = tipoDocumentoSelect.value === 'NIT' || tipoDocumentoSelect.value ===
                    'NIT de otro país';

                if (isNit) {
                    console.log('Mostrando campos de EMPRESA');
                    empresaFields.style.display = 'block';
                    personaFields.style.display = 'none';
                } else {
                    console.log('Mostrando campos de PERSONA NATURAL');
                    empresaFields.style.display = 'none';
                    personaFields.style.display = 'block';
                }
            }

            // Ejecutar al cargar
            toggleFields();

            // Asignar evento
            tipoDocumentoSelect.addEventListener('change', toggleFields);

            console.log('Configuración completada');
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('clienteForm');
            if (!form) return;

            const submitBtn = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', function(e) {
                // Si el form no es válido, deja que el navegador muestre los errores y NO muestres el loader
                if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                    return;
                }

                // Evitar doble clic y poner spinner en el botón
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...';
                }

                // Modal de carga
                Swal.fire({
                    title: 'Guardando cliente...',
                    html: '<div style="font-size:0.95em; color:#666;">Sincronizando</div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                // No hagas preventDefault: dejamos que el form se envíe normalmente
            });
        });
    </script>
@endsection
