@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
    @include('regEmpleado.partials.estilos')
    @php
        $rutaVolver = session('modulo_seleccionado') == 3 ? route('BonoRegalo.inicio') : route('InicioInventario');
    @endphp

    <div class="emp-page container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                        <h3 class="card-title emp-titulo mb-2">
                            <i class="fas fa-users mr-2"></i>Gestión de empleados
                        </h3>
                        <a class="btn btn-emp-primario mb-2" href="{{ route('Reg_Empleado') }}">
                            <i class="fas fa-plus mr-1"></i>Nuevo empleado
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible mx-3 mt-3 mb-0 js-alerta" role="alert">
                            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('success1'))
                        <div class="alert alert-warning alert-dismissible mx-3 mt-3 mb-0 js-alerta" role="alert">
                            <i class="fas fa-exclamation-triangle mr-1"></i>{{ session('success1') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="card-body">
                        @include('Inventario.partials.filtros', [
                            'accion' => route('consultar_Empleado'),
                            'total'  => $empleado->total(),
                            'campos' => [
                                ['tipo' => 'texto',  'nombre' => 'texto', 'placeholder' => 'Cédula, nombre o apellidos'],
                                ['tipo' => 'select', 'nombre' => 'estado', 'etiqueta' => 'Estado', 'todos' => 'Todos', 'opciones' => $estados],
                                ['tipo' => 'select', 'nombre' => 'division', 'etiqueta' => 'División', 'todos' => 'Todas', 'opciones' => $divisiones],
                                ['tipo' => 'select', 'nombre' => 'area', 'etiqueta' => 'Área', 'todos' => 'Todas', 'opciones' => $areas],
                                ['tipo' => 'orden',  'nombre' => 'orden', 'defecto' => 'apellidos', 'opciones' => ['apellidos' => 'Apellidos A-Z', 'nombre' => 'Nombre A-Z', 'recientes' => 'Más recientes']],
                            ],
                        ])

                        @if (count($empleado) === 0)
                            <div class="text-center py-5 emp-ayuda">
                                <i class="fas fa-user-slash fa-2x mb-3"></i>
                                <h5 class="text-dark">No se encontraron empleados</h5>
                                <p class="mb-0">Pruebe con otros términos de búsqueda.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col">Cédula</th>
                                            <th scope="col">Empleado</th>
                                            <th scope="col">Correo</th>
                                            <th scope="col">Área</th>
                                            <th scope="col">Estado</th>
                                            <th scope="col">Módulos</th>
                                            <th scope="col" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($empleado as $item)
                                            @php
                                                $claveEstado = [
                                                    'vigente' => 'emp-badge-vigente',
                                                    'pendiente' => 'emp-badge-pendiente',
                                                    'terminado' => 'emp-badge-terminado',
                                                    'anulado' => 'emp-badge-anulado',
                                                ][mb_strtolower(trim((string) $item->estado))] ?? 'emp-badge-terminado';
                                            @endphp
                                            <tr>
                                                <td class="font-weight-bold text-nowrap">{{ $item->cedula }}</td>
                                                <td>{{ $item->nombre }} {{ $item->apellidos }}</td>
                                                <td>{{ $item->Correo ?: '—' }}</td>
                                                <td>{{ $item->area ?? '—' }}</td>
                                                <td>
                                                    <span class="emp-badge {{ $claveEstado }}">
                                                        {{ $item->estado ?? 'Sin estado' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @forelse ($item->modulos as $nombreModulo)
                                                        <span class="badge emp-badge-modulo mr-1 mb-1">{{ $nombreModulo }}</span>
                                                    @empty
                                                        <span class="emp-ayuda">Sin módulos</span>
                                                    @endforelse
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <a href="{{ route('editar_empleado', ['id' => $item->id]) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Editar empleado">
                                                        <i class="fas fa-edit mr-1"></i>Editar
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                                <p class="emp-ayuda mb-2">
                                    Mostrando {{ $empleado->firstItem() }}–{{ $empleado->lastItem() }} de {{ $empleado->total() }} registros
                                </p>
                                <div class="mb-2">
                                    {{ $empleado->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        @endif

                        <div class="text-center mt-4">
                            <a class="btn btn-outline-secondary px-4" href="{{ $rutaVolver }}">
                                <i class="fas fa-arrow-left mr-1"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
    <script>
        // Las alertas de éxito se cierran solas a los 4 segundos.
        setTimeout(function () {
            $('.js-alerta').alert('close');
        }, 4000);
    </script>
    @endpush
@endsection
