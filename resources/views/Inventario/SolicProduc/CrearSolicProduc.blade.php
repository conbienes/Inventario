@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/lte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/lte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <style>
        .inv-page .swal2-styled.swal2-confirm { background-color: var(--inv-primario, #005484); }
    </style>
@endpush

@push('js')
    <script src="{{ asset('assets/lte/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/lte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@section('content')
    @php
        $solicitante = auth()->user();
        $areaNombre = optional($Area->first())->nombre;
    @endphp

    <div class="inv-cabecera">
        <div>
            <h1 class="inv-titulo">Nueva solicitud de productos</h1>
            <p class="inv-subtitulo">Agregue los productos de su división y envíe la solicitud para aprobación.</p>
        </div>
        <div class="inv-acciones">
            <a href="{{ route('InicioInventario') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong><i class="fas fa-exclamation-triangle"></i> Revise la solicitud:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('actualizar_SolicProduc') }}" id="formSolicitud" novalidate>
        @csrf

        {{-- 1. Datos del solicitante (desde la sesión, no editables) --}}
        <div class="inv-tarjeta-wrap mb-3">
            <div class="inv-tarjeta p-3 p-md-4">
                <h2 class="h5 font-weight-bold mb-3">1. Datos del solicitante</h2>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="solicitante">Solicitante</label>
                        <input type="text" id="solicitante" class="form-control" readonly
                            value="{{ trim($solicitante->nombre . ' ' . $solicitante->apellidos) }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="area">Área solicitante</label>
                        <input type="text" id="area" class="form-control" readonly
                            value="{{ $areaNombre ?? 'Sin área asignada' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Productos --}}
        <div class="inv-tarjeta-wrap mb-3">
            <div class="inv-tarjeta p-3 p-md-4">
                <h2 class="h5 font-weight-bold mb-3">2. Productos</h2>
                <div class="row align-items-end">
                    <div class="form-group col-md-7">
                        <label for="selectProducto">Producto</label>
                        <select id="selectProducto" class="form-control w-100"></select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="inputCantidad">Cantidad</label>
                        <input type="number" id="inputCantidad" class="form-control" min="1" step="1" max="100000" inputmode="numeric">
                    </div>
                    <div class="form-group col-md-2">
                        <button type="button" id="btnAgregar" class="btn btn-primary btn-block">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>
                <p class="inv-ayuda mb-3">Escriba al menos 2 letras del nombre. Se muestran los productos de su división con su existencia. Pulse Enter en la cantidad para agregar.</p>

                <div class="inv-tabla-wrap">
                    <div class="table-responsive">
                        <table class="table inv-tabla mb-0" id="tablaLineas">
                            <thead>
                                <tr>
                                    <th scope="col">Producto</th>
                                    <th scope="col" class="num">Cantidad</th>
                                    <th scope="col" class="text-center">Quitar</th>
                                </tr>
                            </thead>
                            <tbody id="lineas"></tbody>
                        </table>
                    </div>
                </div>
                <p class="inv-vacio mt-2" id="mensajeVacio">Aún no ha agregado productos.</p>
            </div>
        </div>

        {{-- 3. Fecha y observaciones --}}
        <div class="inv-tarjeta-wrap mb-3">
            <div class="inv-tarjeta p-3 p-md-4">
                <h2 class="h5 font-weight-bold mb-3">3. Entrega y observaciones</h2>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="Fecha">Fecha tentativa de entrega <span class="inv-campo-obligatorio">*</span></label>
                        <input type="date" id="Fecha" name="Fecha" class="form-control"
                            min="{{ now()->toDateString() }}" value="{{ old('Fecha') }}" required>
                    </div>
                    <div class="form-group col-md-8">
                        <label for="Observaciones">Observaciones <span class="inv-ayuda">(opcional)</span></label>
                        <textarea id="Observaciones" name="Observaciones" class="form-control" rows="2" maxlength="500">{{ old('Observaciones') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="inv-acciones inv-acciones--fin">
            <a href="{{ route('InicioInventario') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" id="btnEnviar" class="btn btn-success" disabled>
                <i class="fas fa-paper-plane"></i> Enviar solicitud
            </button>
        </div>
    </form>

    @push('js')
    <script>
        @php
            // Si el envío falla la validación, Laravel devuelve los productos escritos: se reconstruyen en la tabla
            $previos = [];
            $idsPrevios = (array) old('IdInventario', []);
            $cantPrevias = (array) old('Cantidad', []);
            $nombresPrevios = (array) old('NombreLinea', []);
            foreach ($idsPrevios as $i => $idPrevio) {
                $previos[] = [
                    'id' => (string) $idPrevio,
                    'nombre' => (string) ($nombresPrevios[$i] ?? ('Producto ' . $idPrevio)),
                    'cantidad' => (int) ($cantPrevias[$i] ?? 1),
                ];
            }
        @endphp
        (function () {
            const MAX_LINEAS = 50;
            const previos = @json($previos);
            const lineas = new Map(); // idInventario -> { nombre, cantidad }
            const $tbody = document.getElementById('lineas');
            const $vacio = document.getElementById('mensajeVacio');
            const $btnEnviar = document.getElementById('btnEnviar');
            const $form = document.getElementById('formSolicitud');
            let productoActual = null; // { id, nombre }

            function alerta(texto) {
                if (window.Swal) {
                    Swal.fire({ icon: 'warning', title: texto, confirmButtonText: 'Entendido' });
                } else {
                    window.alert(texto);
                }
            }

            function escapar(texto) {
                const d = document.createElement('div');
                d.textContent = texto;
                return d.innerHTML;
            }

            function render() {
                $tbody.innerHTML = '';
                lineas.forEach(function (linea, id) {
                    const tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td>' +
                            '<input type="hidden" name="IdInventario[]" value="' + id + '">' +
                            '<input type="hidden" name="NombreLinea[]" value="' + escapar(linea.nombre).replace(/"/g, '&quot;') + '">' +
                            escapar(linea.nombre) +
                        '</td>' +
                        '<td class="num">' +
                            '<input type="hidden" name="Cantidad[]" value="' + linea.cantidad + '">' +
                            linea.cantidad +
                        '</td>' +
                        '<td class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger" data-quitar="' + id + '">Quitar</button>' +
                        '</td>';
                    $tbody.appendChild(tr);
                });
                $vacio.style.display = lineas.size ? 'none' : '';
                $btnEnviar.disabled = lineas.size === 0;
            }

            $('#selectProducto').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Buscar producto…',
                language: {
                    noResults: function () { return 'Producto no encontrado'; },
                    searching: function () { return 'Buscando…'; },
                    inputTooShort: function () { return 'Escriba al menos 2 letras'; }
                },
                minimumInputLength: 2,
                ajax: {
                    url: '{{ url('/Ajax/Inventario') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { q: params.term }; },
                    processResults: function (data) {
                        return {
                            results: (data || []).map(function (p) {
                                return {
                                    id: p.Id,
                                    text: p.Nombre + (p.Codigo ? ' (' + p.Codigo + ')' : '') + ' — existencia: ' + p.Cantidad,
                                    nombre: p.Nombre + (p.Codigo ? ' (' + p.Codigo + ')' : '')
                                };
                            })
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function (e) {
                productoActual = { id: String(e.params.data.id), nombre: e.params.data.nombre };
                document.getElementById('inputCantidad').focus();
            }).on('select2:clear', function () {
                productoActual = null;
            });

            document.getElementById('inputCantidad').addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('btnAgregar').click();
                }
            });

            document.getElementById('btnAgregar').addEventListener('click', function () {
                const cantidadTxt = document.getElementById('inputCantidad').value.trim();
                const cantidad = Number(cantidadTxt);

                if (!productoActual) {
                    return alerta('Seleccione un producto.');
                }
                if (!cantidadTxt || !Number.isInteger(cantidad) || cantidad < 1) {
                    return alerta('La cantidad debe ser un número entero de al menos 1.');
                }
                if (cantidad > 100000) {
                    return alerta('La cantidad máxima por producto es 100.000.');
                }
                if (lineas.has(productoActual.id)) {
                    return alerta('Ese producto ya está en la solicitud. Quítelo para cambiar la cantidad.');
                }
                if (lineas.size >= MAX_LINEAS) {
                    return alerta('Una solicitud admite hasta ' + MAX_LINEAS + ' productos.');
                }

                lineas.set(productoActual.id, { nombre: productoActual.nombre, cantidad: cantidad });
                productoActual = null;
                $('#selectProducto').val(null).trigger('change');
                document.getElementById('inputCantidad').value = '';
                render();
            });

            $tbody.addEventListener('click', function (e) {
                const boton = e.target.closest('[data-quitar]');
                if (boton) {
                    lineas.delete(boton.getAttribute('data-quitar'));
                    render();
                }
            });

            // Evita un doble envío y valida lo mínimo antes de ir al servidor
            $form.addEventListener('submit', function (e) {
                if (lineas.size === 0) {
                    e.preventDefault();
                    return alerta('Agregue al menos un producto.');
                }
                if (!document.getElementById('Fecha').value) {
                    e.preventDefault();
                    return alerta('Indique la fecha tentativa de entrega.');
                }
                $btnEnviar.disabled = true;
                $btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Enviando…';
            });

            previos.forEach(function (p) {
                lineas.set(p.id, { nombre: p.nombre, cantidad: p.cantidad });
            });
            render();
        })();
    </script>
@endpush
@endsection
