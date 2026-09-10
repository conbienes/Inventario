<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor Documental</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="display-6 fw-bold text-primary">
                    <i class="bi bi-folder"></i>Gestion Documental Comercial
                </h1>
            </div>
        </div>

        <!-- Mensajes de éxito o error -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm p-3 mb-4">
            <form method="GET" action="{{ route('Comercial.VistaDelete', $idInmueble) }}">
                <div class="row g-2 align-items-end">
                    <!-- Campo oculto para mantener el inmueble seleccionado -->
                    <input type="hidden" name="id_inmueble_cliente" value="{{ request('id_inmueble_cliente') }}">

                    <!-- Buscar por nombre del documento -->
                    <div class="col-md-3">
                        <label for="nombre" class="form-label">Buscar por Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control"
                            value="{{ request('nombre') }}" placeholder="Ej. Factura, Contrato">
                    </div>

                    <!-- Fecha desde -->
                    <div class="col-md-3">
                        <label for="fecha_desde" class="form-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
                            value="{{ request('fecha_desde') }}">
                    </div>

                    <!-- Fecha hasta -->
                    <div class="col-md-3">
                        <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
                            value="{{ request('fecha_hasta') }}">
                    </div>

                    <!-- Botón Filtrar -->
                    <div class="col-md-3 d-flex">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <table class="table table-striped table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>ID Inmueble</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inmuebles as $inmueble)
                    <tr>
                        <td>{{ $inmueble->id_inmueble_cliente }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="1">No hay inmuebles disponibles.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>



        <!-- Formulario de eliminación -->
        <form id="form-documentos" method="POST" action="{{ route('Comercial.eliminar') }}">
            @csrf
            @method('DELETE')

            <div class="table-responsive">
                <table class="table table-striped table-bordered text-center">
                    <thead class="table-primary">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Ver</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documentos as $doc)
                            <tr id="fila-{{ $doc->id }}">
                                <td>
                                    <input type="checkbox" class="select-documento" name="documentos[]"
                                        value="{{ $doc->id }}">
                                </td>
                                <td>{{ $doc->nombre }}</td>
                                <td>{{ $doc->cod_documental }}</td>
                                <td>{{ $doc->fecha ? $doc->fecha->format('Y-m-d') : '' }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="abrirPDF('{{ asset($doc->url) }}')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="confirmarEliminar({{ $doc->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No hay documentos disponibles</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if ($documentos instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-4 d-flex justify-content-center">
                    {{ $documentos->appends(request()->query())->links() }}
                </div>
            @endif

            <div class="d-flex justify-content-center gap-3 mt-3">
                <button type="button" id="btn-eliminar-seleccionados" class="btn btn-success" disabled>
                    <i class="bi bi-trash-fill"></i> Eliminar Seleccionados
                </button>
                <a href="{{ route('Comercial.index', ['id_inmueble_cliente' => $inmuebles->first()->sharepoint_id ?? '']) }}"
                    class="btn btn-danger">
                    <i class="bi bi-backspace"></i> Salir
                </a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Abrir el PDF en una nueva pestaña
        function abrirPDF(url) {
            window.open(url, '_blank');
        }

        // Confirmar eliminación de un solo documento
        function confirmarEliminar(id) {
            if (confirm("¿Seguro que deseas eliminar este documento?")) {
                let form = document.getElementById('form-documentos');
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'documentos[]';
                input.value = id;
                form.appendChild(input);
                form.submit();
            }
        }

        // Seleccionar/Deseleccionar todos los checkboxes
        document.getElementById('select-all').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.select-documento');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
            document.getElementById('btn-eliminar-seleccionados').disabled = !this.checked;
        });

        // Habilitar botón de eliminación cuando haya documentos seleccionados
        document.querySelectorAll('.select-documento').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                let seleccionado = document.querySelectorAll('.select-documento:checked').length > 0;
                document.getElementById('btn-eliminar-seleccionados').disabled = !seleccionado;
            });
        });

        // Eliminar documentos seleccionados
        document.getElementById('btn-eliminar-seleccionados').addEventListener('click', function() {
            if (confirm("¿Seguro que deseas eliminar los documentos seleccionados?")) {
                document.getElementById('form-documentos').submit();
            }
        });
    </script>
</body>

</html>
