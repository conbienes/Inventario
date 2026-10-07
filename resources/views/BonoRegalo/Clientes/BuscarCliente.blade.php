@extends("theme.$theme.layout")

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="card shadow-sm">
                    <div
                        class="card-header bg-primary text-white d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <h3 class="card-title mb-2 mb-md-0">
                            <i class="fas fa-users mr-2"></i>Clientes Bono Regalo
                        </h3>
                        <a href="{{ route('BonoRegalo.IndexCliente') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus-circle mr-1"></i> Nuevo Cliente
                        </a>
                    </div>
                    @php
                        $success = session('success');
                        $error = session('error');

                        // Normaliza el success a HTML aunque venga como array
                        if (is_array($success)) {
                            $successHtml = '<ul style="text-align:left;margin:0;padding-left:1.2em;">';
                            foreach ($success as $k => $v) {
                                $item = is_string($k) ? "<strong>{$k}:</strong> {$v}" : e($v);
                                $successHtml .= "<li>{$item}</li>";
                            }
                            $successHtml .= '</ul>';
                        } elseif (is_string($success)) {
                            $successHtml = e($success);
                        } else {
                            $successHtml = null;
                        }
                    @endphp

                    @if ($successHtml)
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    title: `<span style="font-size:1.1em; font-weight:600; color:#28a745;">Éxito</span>`,
                                    icon: 'success',
                                    html: {!! json_encode($successHtml) !!},
                                    confirmButtonText: 'Entendido',
                                    focusConfirm: true
                                });
                            });
                        </script>
                    @endif

                    @if ($error)
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    title: `<span style="font-size:1.1em; font-weight:600; color:#d9534f;">Error</span>`,
                                    icon: 'error',
                                    html: {!! json_encode(is_array($error) ? implode('<br>', $error) : $error) !!},
                                    confirmButtonText: 'Cerrar',
                                    focusConfirm: true
                                });
                            });
                        </script>
                    @endif

                    <!-- Barra de búsqueda mejorada -->
                    <div class="card-tools p-3 bg-light">
                        <form action="{{ route('BonoRegalo.BuscarCliente') }}" method="GET">
                            <div class="input-group input-group-lg">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Buscar por cédula, nombre o correo..." value="{{ request('search') }}"
                                    aria-label="Buscar clientes">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    @if (request()->has('search'))
                                        <a href="{{ route('BonoRegalo.BuscarCliente') }}" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0" id="tablaClientes">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th width="5%">ID</th>
                                        <th width="15%">Cédula</th>
                                        <th width="20%">Nombre</th>
                                        <th width="20%">Apellidos</th>
                                        <th width="20%">Correo</th>
                                        <th width="10%">Tipo</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($clientes as $cliente)
                                        <tr>
                                            <td class="text-center align-middle">{{ $cliente->id }}</td>
                                            <td class="align-middle">{{ $cliente->cedula }}</td>
                                            <td class="align-middle">{{ $cliente->nombre }} {{ $cliente->segundo_nombre }}
                                            </td>
                                            <td class="align-middle">{{ $cliente->apellidos }}
                                                {{ $cliente->segundo_apellido }}</td>
                                            <td class="align-middle">
                                                <a href="mailto:{{ $cliente->correo }}">{{ $cliente->correo }}</a>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span
                                                    class="badge {{ $cliente->tipoActividad === 'CLI-NRI' ? 'badge-info' : 'badge-success' }}">
                                                    {{ $cliente->tipoActividad === 'CLI-NRI' ? 'Natural' : 'Jurídico' }}
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('BonoRegalo.editarCliente', $cliente->id) }}"
                                                        class="btn btn-outline-primary" title="Editar"
                                                        data-toggle="tooltip">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    {{-- Eliminar cliente: deshabilitado (la ruta no existe; un cliente con facturas no debe borrarse)
                                                    <form action="#"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('¿Estás seguro de eliminar este cliente?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-outline-danger"
                                                                title="Eliminar"
                                                                data-toggle="tooltip">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form> --}}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-user-slash fa-2x mb-2 text-muted"></i>
                                                <h5 class="text-muted">No se encontraron clientes</h5>
                                                @if (request()->has('search'))
                                                    <a href="{{ route('BonoRegalo.Index.Cliente') }}"
                                                        class="btn btn-sm btn-warning mt-2">
                                                        <i class="fas fa-undo mr-1"></i> Ver todos
                                                    </a>
                                                @else
                                                    <a href="{{ route('BonoRegalo.IndexCliente') }}"
                                                        class="btn btn-sm btn-primary mt-2">
                                                        <i class="fas fa-plus mr-1"></i> Agregar Cliente
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if (method_exists($clientes, 'links'))
                        <div class="card-footer d-flex justify-content-center">
                            {{ $clientes->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Estilos responsivos */
        @media (max-width: 768px) {
            #tablaClientes {
                font-size: 0.85rem;
            }

            #tablaClientes th,
            #tablaClientes td {
                padding: 0.5rem;
            }

            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
            }

            .input-group-lg>.form-control,
            .input-group-lg>.input-group-append>.btn {
                height: calc(2.5rem + 2px);
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }

        .table thead th {
            white-space: nowrap;
        }

        .card-header {
            padding: 0.75rem 1.25rem;
        }

        .badge {
            font-size: 0.85em;
            font-weight: 500;
            padding: 0.35em 0.65em;
        }

        .card-tools {
            border-bottom: 1px solid rgba(0, 0, 0, .125);
        }
    </style>
@endpush


<script>
    $(document).ready(function() {
        // Inicializar tooltips
        $('[data-toggle="tooltip"]').tooltip({
            trigger: 'hover'
        });

        // Opcional: DataTables para funcionalidad avanzada
        if ($('#tablaClientes').length && {{ count($clientes) }} > 10) {
            $('#tablaClientes').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                },
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                pageLength: 25,
                // Deshabilitar búsqueda si ya tenemos nuestro propio campo
                searching: false
            });
        }

        // Auto-focus en el campo de búsqueda
        $('input[name="search"]').focus();
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
