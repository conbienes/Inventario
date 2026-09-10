@extends("theme.$theme.layout")

@section('content')
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #6f42c1;
            --success-color: #1cc88a;
            --light-bg: #f8f9fc;
        }

        body {
            background-color: var(--light-bg);
            color: #5a5c69;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1.2rem;
        }

        .form-control,
        .custom-select {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d3e2;
        }

        .form-control:focus,
        .custom-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 0.35rem;
        }

        .btn-primary:hover {
            background-color: #3a5fc8;
            border-color: #3a5fc8;
        }

        .btn-secondary {
            background-color: #858796;
            border-color: #858796;
            padding: 0.5rem 1.5rem;
            border-radius: 0.35rem;
        }

        .btn-secondary:hover {
            background-color: #717384;
            border-color: #717384;
        }

        .alert {
            border-radius: 0.35rem;
            border: none;
        }

        .module-card {
            transition: all 0.3s;
            border: 1px solid #e3e6f0;
        }

        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .section-title {
            border-left: 4px solid var(--primary-color);
            padding-left: 10px;
            margin: 25px 0 15px;
            color: var(--primary-color);
        }

        .password-note {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-user-edit me-2"></i>{{ __('Editar Empleado') }}:
                            {{ $empleado->nombre . ' ' . $empleado->apellidos }}
                        </h3>
                    </div>

                    {{-- Mensajes de error --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible m-3">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <h5><i class="fas fa-exclamation-circle me-2"></i>Error en el formulario</h5>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('actualizar_empleado', ['id' => $empleado->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="card-body p-4">
                            <h5 class="section-title">Información Personal</h5>
                            <div class="row">
                                <div class="form-group col-md-6 mb-3">
                                    <label for="cedula" class="form-label fw-bold">Cédula</label>
                                    <input type="number" class="form-control" name="cedula"
                                        value="{{ old('cedula', $empleado->cedula) }}" placeholder="Ingrese la cédula">
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="nombre" class="form-label fw-bold">Nombre</label>
                                    <input type="text" class="form-control" name="nombre"
                                        value="{{ old('nombre', $empleado->nombre) }}" placeholder="Ingrese el nombre">
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="apellidos" class="form-label fw-bold">Apellidos</label>
                                    <input type="text" class="form-control" name="apellidos"
                                        value="{{ old('apellidos', $empleado->apellidos) }}" placeholder="Ingrese los apellidos">
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="email" class="form-label fw-bold">Correo Electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email', $empleado->Correo) }}" placeholder="ejemplo@correo.com">
                                    </div>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label for="password" class="form-label fw-bold">Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" name="password" class="form-control"
                                            placeholder="Dejar en blanco para mantener la actual">
                                    </div>
                                    <div class="password-note">
                                        <i class="fas fa-info-circle me-1"></i> Solo complete si desea cambiar la contraseña
                                    </div>
                                </div>
                            </div>

                            <h5 class="section-title">Información Laboral</h5>
                            <div class="row">
                                <div class="form-group col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tipo de Empleado</label>
                                    <select class="form-select" name="id_tipoempleado">
                                        @foreach ($TipoEmpleado as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('id_tipoempleado', $empleado->id_tipoempleado) == $item->id ? 'selected' : '' }}>
                                                {{ $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label class="form-label fw-bold">Área</label>
                                    <select class="form-select" name="id_area">
                                        @foreach ($area as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('id_area', $empleado->id_area) == $item->id ? 'selected' : '' }}>
                                                {{ $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label class="form-label fw-bold">División</label>
                                    <select class="form-select" name="id_div">
                                        @foreach ($division as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('id_div', $empleado->id_div) == $item->id ? 'selected' : '' }}>
                                                {{ $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label class="form-label fw-bold">Estado</label>
                                    <select class="form-select" name="id_estado">
                                        @foreach ($estado as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('id_estado', $empleado->id_estado) == $item->id ? 'selected' : '' }}>
                                                {{ $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6 mb-3">
                                    <label class="form-label fw-bold">Sexo</label>
                                    <select class="form-select" name="id_sexo">
                                        @foreach ($sexo as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('id_sexo', $empleado->id_sexo) == $item->id ? 'selected' : '' }}>
                                                {{ $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <h5 class="section-title">Permisos del Sistema</h5>
                            <div class="form-group mb-4">
                                <div class="card p-3 bg-light">
                                    <div class="row">
                                        @foreach ($modulos as $modulo)
                                            <div class="col-md-6 mb-3">
                                                <div class="module-card p-3 bg-white rounded">
                                                    <strong class="d-block mb-2 text-primary">
                                                        <i class="fas fa-cube me-1"></i>{{ $modulo->nombre }}
                                                    </strong>
                                                    @foreach ($permisos as $permiso)
                                                        <div class="form-check form-switch mb-2">
                                                            <input type="checkbox"
                                                                name="modulos[{{ $modulo->id }}][]"
                                                                value="{{ $permiso->id }}"
                                                                class="form-check-input"
                                                                id="mod{{ $modulo->id }}perm{{ $permiso->id }}"
                                                                {{ is_array(old("modulos.{$modulo->id}", $permisosAsignados[$modulo->id] ?? [])) && in_array($permiso->id, old("modulos.{$modulo->id}", $permisosAsignados[$modulo->id] ?? [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="mod{{ $modulo->id }}perm{{ $permiso->id }}">
                                                                {{ $permiso->nombre }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white py-3 d-flex justify-content-center gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Actualizar Empleado
                            </button>
                            @php
                                $modulo = session('modulo_seleccionado');
                                switch ($modulo) {
                                    case 1:
                                    case 2:
                                        $ruta = route('InicioInventario');
                                        break;
                                    case 3:
                                        $ruta = route('BonoRegalo.inicio');
                                        break;
                                    default:
                                        $ruta = route('InicioInventario');
                                        break;
                                }
                            @endphp
                            <a class="btn btn-secondary" href="{{ $ruta }}">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
@endsection
