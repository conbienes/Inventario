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

        input[name^="documentos"][name$="[archivo]"] {
            width: 350px;
            /* Espacio para 4 dígitos */
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
                    <i class="bi bi-files"></i> Anexar Documentos Comercial
                </h1>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <div class="container">
            @forelse ($inmuebles as $inmueble)
                <h4 class="mb-4">Cargar Documentos {{ $inmueble->NOMENCLATURA }}</h4>
            @empty
                <tr>
                    <td colspan="{{ session('documental') ? 2 : 1 }}" class="text-center text-muted">No hay inmuebles
                        seleccionados</td>
                </tr>
            @endforelse


            <form action="{{ route('ExpInmueble.subir') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                            <th>Id</th>
                        </tr>
                    </thead>
                    <tbody id="documentos-container">
                        @forelse (old('documentos', []) as $index => $documento)
                            <tr class="documento-row">
                                <td>
                                    <input type="file" name="documentos[{{ $index }}][archivo]"
                                        class="form-control" required>
                                    @error("documentos.$index.archivo")
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </td>
                                <td>
                                    <input type="text" name="documentos[{{ $index }}][nombre]"
                                        class="form-control" value="{{ old("documentos.$index.nombre") }}" required>
                                    @error("documentos.$index.nombre")
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </td>
                                <td>
                                    <input type="text" name="documentos[{{ $index }}][cod_documental]"
                                        class="form-control" required
                                        value="{{ old("documentos.$index.cod_documental") }}">
                                    @error("documentos.$index.cod_documental")
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </td>
                                <td>
                                    <input type="date" name="documentos[{{ $index }}][fecha]"
                                        class="form-control" required value="{{ old("documentos.$index.fecha") }}">
                                    @error("documentos.$index.fecha")
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button>
                                    <input type="hidden" name="documentos[{{ $index }}][id_inmClient]"
                                        value="{{ old("documentos.$index.id_inmClient", $inmuebles->first()->idExpInmueble ?? '') }}">
                                </td>
                            </tr>
                        @empty
                            {{-- Si no hay documentos previos, agregamos una fila vacía --}}
                            <tr class="documento-row">
                                <td>
                                    <input type="file" name="documentos[0][archivo]" class="form-control" required>
                                </td>
                                <td>
                                    <input type="text" name="documentos[0][nombre]" class="form-control" required>
                                </td>
                                <td>
                                    <input type="text" name="documentos[0][cod_documental]" class="form-control"
                                        required>
                                </td>
                                <td>
                                    <input type="date" name="documentos[0][fecha]" class="form-control" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button>
                                </td>
                                <td>
                                    <input type="text" value="{{ $inmuebles[0]->idExpInmueble }}"
                                        name="documentos[0][id_inmClient]" class="form-control form-control-sm" readonly
                                        style="width: 70px;">
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center gap-3 mt-3">
                    <button type="button" id="add-documento" class="btn btn-primary">Agregar Documento</button>
                    <button type="submit" class="btn btn-success">Subir Documentos</button>
                    <a href="{{ route('ExpInmueble.index', ['ExpInmueble' => $inmuebles->first()->idExpInmueble ?? '']) }}"
                        class="btn btn-danger">
                        <i class="bi bi-backspace"></i> Salir
                    </a>
                </div>
            </form>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let index = 1; // Para manejar los índices dinámicos
            let sharepointId = "{{ $inmuebles->first()->sharepoint_id ?? '' }}"; // Captura el sharepoint_id

            document.getElementById("add-documento").addEventListener("click", function() {
                let container = document.getElementById("documentos-container");
                let newRow = document.createElement("tr");
                newRow.classList.add("documento-row");
                newRow.innerHTML = `
            <td><input type="file" name="documentos[${index}][archivo]" class="form-control" required></td>
            <td><input type="text" name="documentos[${index}][nombre]" class="form-control" required></td>
            <td><input type="text" name="documentos[${index}][cod_documental]" class="form-control"></td>
            <td><input type="date" name="documentos[${index}][fecha]" class="form-control"></td>
            <td><input type="hidden" name="documentos[${index}][id_inmClient]" value="${sharepointId}"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button></td>
        `;
                container.appendChild(newRow);
                index++;
            });

            document.getElementById("documentos-container").addEventListener("click", function(event) {
                if (event.target.classList.contains("remove-row")) {
                    event.target.closest("tr").remove();
                }
            });
        });
    </script>

</body>

</html>
