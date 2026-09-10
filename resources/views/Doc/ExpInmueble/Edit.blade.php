<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor Documental</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">

    <style>
        /* General */
        .document-list-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            transition: all 0.3s ease;
        }

        /* Tamaños para computadoras */
        .nombre-doc {
            width: 750px;
            /* Nombre más largo */
            min-width: 300px;
            max-width: 100%;
        }

        input[name^="documentos"][name$="[cod_documental]"] {
            width: 80px;
            /* Espacio para 4 dígitos */
            text-align: center;
        }

        input[name^="documentos"][name$="[fecha]"] {
            width: 150px;
            /* Espacio suficiente para dd/mm/yyyy */
            text-align: center;
        }

        /* Centrar botón "Ver" */
        .btn-ver {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            padding: 0;
        }

        /* Ajustes para dispositivos móviles */
        @media (max-width: 768px) {
            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 5px;
            }

            /* Hacer el nombre más pequeño en celulares */
            .nombre-doc {
                width: 100%;
                min-width: 180px;
                /* Más pequeño en móvil */
                max-width: 100%;
            }

            input[name^="documentos"][name$="[cod_documental]"] {
                width: 100px;
                /* Ajustado para móvil */
            }

            input[name^="documentos"][name$="[fecha]"] {
                width: 150px;
                /* Ajustado para móvil */
            }

            /* Centrar el botón "Ver" en móviles */
            .btn-ver {
                width: 35px;
                height: 35px;
            }
        }

        /* Optimización para pantallas muy pequeñas (menos de 576px) */
        @media (max-width: 576px) {
            .table thead {
                display: none;
            }

            .table tbody tr {
                display: flex;
                flex-direction: column;
                border-bottom: 2px solid #ddd;
                padding-bottom: 10px;
                margin-bottom: 10px;
            }

            .table tbody td {
                display: flex;
                justify-content: space-between;
                padding: 5px;
                border-bottom: 1px solid #ddd;
            }

            .table tbody td:last-child {
                border-bottom: none;
                justify-content: center;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="display-6 fw-bold text-primary">
                    <i class="bi bi-pencil"></i> Editar ExpInmueble
                </h1>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="card shadow-sm p-3 mb-4">
            <form method="GET" action="{{ route('ExpInmueble.edit', $idInmueble) }}">
                <div class="row g-2 align-items-end">
                    <!-- Campo oculto para mantener el inmueble seleccionado -->
                    <input type="hidden" name="ExpInmueble" value="{{ request('ExpInmueble') }}">

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
                            <td>{{ $inmueble->NOMENCLATURA }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="1">No hay inmuebles disponibles.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>




            <form id="form-documentos" method="POST" action="{{ route('ExpInmueble.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" id="documentos_editados" name="documentos_editados">

                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>Nombre</th>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Ver</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documentos as $doc)
                                <tr id="fila-{{ $doc->id }}">
                                    <input type="hidden" class="form-control nombre-doc"
                                        name="documentos[{{ $doc->id }}][IdSharepoint]"
                                        value="{{ $doc->drive_item_id }}">
                                        <input type="hidden" class="form-control nombre-doc"
                                        name="documentos[{{ $doc->id }}][Tipo]"
                                        value="{{$doc->file_type}}">



                                    <td>
                                        <input type="text" class="form-control nombre-doc"
                                            name="documentos[{{ $doc->id }}][nombre]" value="{{ $doc->nombre }} "
                                            onchange="marcarEditado({{ $doc->id }})">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm"
                                            name="documentos[{{ $doc->id }}][cod_documental]"
                                            value="{{ $doc->cod_documental }}"
                                            onchange="marcarEditado({{ $doc->id }})">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm"
                                            name="documentos[{{ $doc->id }}][fecha]"
                                            value="{{ $doc->fecha ? $doc->fecha->format('Y-m-d') : '' }}"
                                            onchange="marcarEditado({{ $doc->id }})">
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="abrirPDF('{{ asset($doc->url) }}')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay documentos disponibles
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($documentos instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $documentos->appends(request()->query())->links() }}
                    </div>
                @endif

                <div class="d-flex justify-content-center gap-3 mt-3">
                    <button type="submit" id="btn-guardar-uno" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('ExpInmueble.index', ['ExpInmueble' => $inmuebles->first()->idExpInmueble ?? '']) }}"
                        class="btn btn-danger">
                        <i class="bi bi-backspace"></i> Salir
                    </a>
                </div>

            </form>
        </div>

        <!-- Scripts -->
        <script>
            function abrirPDF(url) {
                window.open(url, '_blank');
            }

            let documentosEditados = new Set();

            function marcarEditado(id) {
                documentosEditados.add(id);
                document.getElementById("documentos_editados").value = Array.from(documentosEditados).join(",");
            }

            document.getElementById("form-documentos").addEventListener("submit", function(event) {
                if (documentosEditados.size === 0) {
                    event.preventDefault();
                    alert("Debes modificar al menos un documento antes de guardar.");
                }
            });

            let cambiosPendientes = false;

            document.querySelectorAll('input, select').forEach(element => {
                element.addEventListener('change', () => {
                    cambiosPendientes = true;
                });
            });

            document.getElementById('form-documentos').addEventListener('submit', () => {
                cambiosPendientes = false;
            });

            document.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', (event) => {
                    if (cambiosPendientes) {
                        event.preventDefault();
                        if (confirm("Tienes cambios sin guardar. ¿Quieres continuar sin guardar?")) {
                            window.location.href = link.href;
                        }
                    }
                });
            });
        </script>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
