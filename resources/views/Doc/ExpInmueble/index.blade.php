<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor Documental</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">

    <style>
        .document-list-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            transition: all 0.3s ease;
        }

        .document-actions {
            min-width: 120px;
        }

        /* Ajustes para móviles */
        @media (max-width: 768px) {
            .form-label {
                font-size: 14px;
            }

            .btn {
                font-size: 14px;
                padding: 8px;
            }

            .document-actions {
                min-width: 90px;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <!-- Título -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="display-6 fw-bold text-primary">
                    <i class="bi bi-files"></i> Expediente del Inmueble
                </h1>
            </div>
        </div>
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <!-- Filtro de Inmuebles -->
        <div class="card shadow-sm p-3 mb-4">
            <form method="GET" action="{{ route('ExpInmueble.index') }}">

                <div class="row g-2">
                    <!-- Campo de búsqueda -->
                    <div class="col-md-6 col-12">
                        <label for="lista-inmuebles" class="form-label">Buscar Inmueble</label>
                        <select name="ExpInmueble" id="lista-inmuebles" class="form-control">
                            <option value="">Escribe para buscar...</option>
                            @foreach ($inmuebles as $inmueble)
                                <option value="{{ $inmueble->idExpInmueble }}"
                                    {{ request('ExpInmueble') == $inmueble->idExpInmueble ? 'selected' : '' }}>
                                    {{ $inmueble->NOMENCLATURA }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <!-- Botón Filtrar y Borrar -->
                    <div class="col-md-3 col-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-md-3 col-6 d-flex align-items-end">
                        <a href="{{ url('/ExpInmueble') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Borrar Filtro
                        </a>
                    </div>
                </div>

                <!-- Filtros adicionales solo si hay un inmueble seleccionado -->
                @if (request('ExpInmueble'))
                    <div class="row g-2 mt-3">

                        <!-- Campo oculto para mantener el inmueble seleccionado -->
                        <input type="hidden" name="ExpInmueble" value="{{ request('ExpInmueble') }}">

                        <!-- Buscar por nombre del documento -->
                        <div class="col-md-4">
                            <label for="nombre" class="form-label">Buscar por Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control"
                                value="{{ request('nombre') }}" placeholder="Ej. Factura, Contrato">
                        </div>

                        <!-- Fecha desde -->
                        <div class="col-md-4">
                            <label for="fecha_desde" class="form-label">Fecha Desde</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
                                value="{{ request('fecha_desde') }}">
                        </div>

                        <!-- Fecha hasta -->
                        <div class="col-md-4">
                            <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
                                value="{{ request('fecha_hasta') }}">
                        </div>
                    </div>
                @endif
            </form>
        </div>


        <!-- Listado de Documentos -->
        <div id="documentos-container">
            @include('Doc.ExpInmueble.partials.lista-documentos', ['documentos' => $documentos])
        </div>

        <!-- Paginación centrada -->
        @if ($documentos instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4 d-flex justify-content-center">
                {{ $documentos->appends(request()->query())->links() }}
            </div>
        @endif

    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script>
        function abrirPDF(url) {
            window.open(url, '_blank');
        }

        $(document).ready(function() {
            var preselected = $('input[name="ExpInmueble"]').val();

            $('#lista-inmuebles').select2({
                placeholder: "Escribe para buscar...",
                width: '100%',
                ajax: {
                    url: '/filtrar-ExpInmueble',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            query: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.idExpInmueble,
                                    text: item.NOMENCLATURA
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            // Si hay un valor preseleccionado, agrégalo manualmente
            if (preselected) {
                var preselectedText = $('#lista-inmuebles option:selected').text();
                var option = new Option(preselectedText, preselected, true, true);
                $('#lista-inmuebles').append(option).trigger('change');
            }

            // Evento para actualizar el input hidden cuando cambia el select
            $('#lista-inmuebles').on('change', function() {
                var selectedValue = $(this).val();
                $('input[name="ExpInmueble"]').val(selectedValue);
            });
        });
    </script>
</body>

</html>
