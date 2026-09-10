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
                <h4 class="mb-4">Cargar Documentos {{ $inmueble->id_inmueble_cliente }}</h4>
            @empty
                <tr>
                    <td colspan="{{ session('documental') ? 2 : 1 }}" class="text-center text-muted">No hay inmuebles
                        seleccionados</td>
                </tr>
            @endforelse


            <form action="{{ route('Comercial.subir') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Seleccionar Archivos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="file" name="documentos[]" class="form-control" multiple required
                                    id="archivo-input">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Lista de archivos seleccionados -->
                <div id="lista-archivos" class="mt-3"></div>

                    <input type="hidden" class="form-control nombre-doc" name="id_inmueble_cliente"
                        value="{{ $inmuebles->first()->sharepoint_id }}">

                <div class="d-flex justify-content-center gap-3 mt-3">
                    <button type="submit" class="btn btn-success">Subir Documentos</button>
                    <a href="{{ route('Comercial.index', ['id_inmueble_cliente' => $inmuebles->first()->sharepoint_id ?? '']) }}"
                        class="btn btn-danger">
                        <i class="bi bi-backspace"></i> Salir
                    </a>
                </div>
            </form>

        </div>
    </div>
</body>

</html>

<script>
    document.getElementById("archivo-input").addEventListener("change", function(event) {
        let listaArchivos = document.getElementById("lista-archivos");
        listaArchivos.innerHTML = ""; // Limpiar lista anterior

        if (event.target.files.length > 0) {
            let ul = document.createElement("ul");
            ul.classList.add("list-group");

            Array.from(event.target.files).forEach(file => {
                let li = document.createElement("li");
                li.classList.add("list-group-item", "d-flex", "justify-content-between",
                    "align-items-center");
                li.textContent = file.name;

                let sizeSpan = document.createElement("span");
                sizeSpan.classList.add("badge", "bg-primary", "rounded-pill");
                sizeSpan.textContent = (file.size / 1024).toFixed(2) + " KB"; // Mostrar tamaño en KB

                li.appendChild(sizeSpan);
                ul.appendChild(li);
            });

            listaArchivos.appendChild(ul);
        }
    });
</script>
