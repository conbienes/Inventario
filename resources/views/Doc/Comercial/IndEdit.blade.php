<!DOCTYPE html>
<html lang="es" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Documento</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        .compact-form .form-control {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .compact-form label {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .card-custom {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }

        .table-compact {
            font-size: 0.875rem;
        }

        .table-compact td,
        .table-compact th {
            padding: 0.5rem;
        }

        .scrollable-table {
            max-height: 200px;
            overflow-y: auto;
        }

        .section-title {
            font-size: 1.1rem;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .scrollable-table {
                max-height: 150px;
            }

            .section-title {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body class="d-flex flex-column h-100 bg-light">
    <div class="container py-3 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Encabezado -->
                <div class="text-center mb-4">
                    <h2 class="h4 fw-bold text-primary">
                        <i class="bi bi-file-earmark-edit"></i> Editar Documento
                    </h2>
                </div>

                <!-- Alertas -->
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
                        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Sección Inmuebles -->
                <div class="card card-custom mb-4">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0"><i class="bi bi-house-door"></i> Inmuebles Relacionados</h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="scrollable-table">
                            <table class="table table-compact table-hover mb-0">
                                <tbody>
                                    @forelse ($inmuebles as $inmueble)
                                        <tr>
                                            <td class="text-truncate">{{ $inmueble->id_inmueble_cliente }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-muted">No hay inmuebles</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Edición -->
                <div class="card card-custom">
                    <div class="card-body compact-form">
                        <form id="form-documentos" method="POST" action="{{ route('Comercial.ActualizarIndiv') }}">
                            @csrf
                            @method('PUT')

                            <!-- Archivo Actual -->
                            <div class="mb-4">
                                <label class="form-label">Archivo Actual</label>
                                <div class="d-grid">
                                    <a href="{{ asset($documento->url) }}" target="_blank"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-file-earmark-arrow-down"></i> Ver Documento
                                    </a>
                                </div>
                            </div>

                            <input type="hidden" class="form-control nombre-doc"
                                name="drive_item_id"
                                value="{{ $documento->drive_item_id }}">

                            <input type="hidden" class="form-control nombre-doc"
                                name="ext" value="{{ $documento->file_type }}">


                                <input type="hidden" class="form-control nombre-doc"
                                name="id" value="{{ $documento->id }}">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="nombre" class="form-label">Nombre del Documento</label>
                                    <input type="text" class="form-control form-control-sm" id="nombre"
                                        name="nombre" value="{{ old('nombre', $documento->nombre) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="cod_documental" class="form-label">Código Documental</label>
                                    <input type="text" class="form-control form-control-sm" id="cod_documental"
                                        name="cod_documental"
                                        value="{{ old('cod_documental', $documento->cod_documental) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="fecha" class="form-label">Fecha</label>
                                    <input type="date" class="form-control form-control-sm" id="fecha"
                                        name="fecha"    value="{{ $documento->fecha ? $documento->fecha->format('Y-m-d') : '' }}" required>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="mt-4 d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-save"></i> Guardar Cambios
                                </button>
                                <a href="{{ route('Comercial.index', ['id_inmueble_cliente' => $inmuebles->first()->sharepoint_id ?? '']) }}"
                                    class="btn btn-danger btn-sm">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
