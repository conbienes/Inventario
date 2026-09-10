@extends("theme.$theme.layout")

@section('content')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card card-primary shadow">
                    <div class="card-header bg-gradient-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-users mr-2"></i>{{ __('GESTIÓN DE EMPLEADOS') }}
                        </h3>
                    </div>

                    <!-- Alertas -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert"
                            data-auto-dismiss="3000">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>{{ session('success') }}</span>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('success1'))
                        <div class="alert alert-warning alert-dismissible fade show mx-3 mt-3" role="alert"
                            data-auto-dismiss="3000">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span>{{ session('success1') }}</span>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Barra de búsqueda -->
                    <div class="card-body">
                        <form action="{{ route('consultar_Empleado') }}" method="GET">
                            <div class="row align-items-center mb-4">
                                <div class="col-md-8 mb-2">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">
                                                <i class="fas fa-search text-primary"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control form-control-lg" name="texto"
                                            id="texto" placeholder="Buscar por cédula, nombre o apellido..."
                                            autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="submit" name="Buscar" id="Buscar" class="btn btn-primary btn-block">
                                        <i class="fas fa-search mr-1"></i> {{ __('Buscar') }}
                                    </button>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <a class="btn btn-success btn-block" name="Crear" id="Crear"
                                        href="{{ route('crear_Empleado') }}">
                                        <i class="fas fa-plus mr-1"></i> {{ __('Crear') }}
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Tabla de empleados -->
                        <div class="table-responsive">
                            @if (count($empleado) <= 0)
                                <div class="alert alert-info text-center py-4">
                                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                                    <h4>No se encontraron empleados</h4>
                                    <p class="mb-0">Intente con otros términos de búsqueda o cree un nuevo empleado.</p>
                                </div>
                            @else
                                <table class="table table-hover table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col" class="text-center">Cédula</th>
                                            <th scope="col">Nombre</th>
                                            <th scope="col">Apellido</th>
                                            <th scope="col" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($empleado as $item)
                                            <tr>
                                                <td class="text-center font-weight-bold">{{ $item->cedula }}</td>
                                                <td>{{ $item->nombre }}</td>
                                                <td>{{ $item->apellidos }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('editar_empleado', ['id' => $item->id]) }}"
                                                        class="btn btn-sm btn-outline-primary" data-toggle="tooltip"
                                                        title="Editar empleado">
                                                        <i class="fas fa-edit mr-1"></i> Editar
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>

                        <!-- Paginación -->
                        @if (count($empleado) > 0)
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted">
                                    <p class="mb-0">Mostrando {{ $empleado->count() }} de {{ $empleado->total() }}
                                        registros</p>
                                </div>
                                <div>
                                    {{ $empleado->appends(request()->input())->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        @endif

                        <!-- Botón cancelar -->
                        <div class="text-center mt-4">
                            @php
                                $modulo = session('modulo_seleccionado');
                                switch ($modulo) {
                                    case 1:
                                        $ruta = route('InicioInventario');
                                        break;
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
                            <a class="btn btn-secondary px-4" href="{{ $ruta }}">
                                <i class="fas fa-arrow-left mr-1"></i></i>Cancelar
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card-header {
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            background-color: #f8f9fa;
        }

        .btn {
            border-radius: 0.35rem;
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 0.5rem;
            border: none;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .pagination .page-item.active .page-link {
            background-color: #4e73df;
            border-color: #4e73df;
        }
    </style>

    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();

            // Auto-dismiss alerts after 3 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 3000);
        });
    </script>

@endsection
