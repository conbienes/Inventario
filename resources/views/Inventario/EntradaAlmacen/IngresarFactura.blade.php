@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/lte/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/lte/plugins/sweetalert2/sweetalert2.min.css') }}">
<style>
    .custom-dropdown-scroll {
        max-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>
@endpush

@push('js')
<script src="{{ asset('assets/lte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/lte/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
        $('.Inventario1').select2({
            theme: "bootstrap4",
            language: {
                noResults: function() {
                    return "Producto no encontrado";
                },
                searching: function() {
                    return "Buscando.......";
                }
            },
            ajax: {
                url: "{{ url('/Ajax/Inventario1') }}",
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.Codigo + " | " + item.Nombre,
                                id: item.Id
                            };
                        })
                    };
                },
                cache: true
            },
            dropdownCssClass: "custom-dropdown-scroll"
        });
    
</script>
<script>
        $('.Inventario2').select2({
            theme: "bootstrap4",
            language: {
                noResults: function() {
                    return "Producto no encontrado";
                },
                searching: function() {
                    return "Buscando......."
                }
            },
            ajax: {
                url: "{{ url('/Ajax/Inventario2') }}",
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.Codigo + " | " + item.Nombre,
                                id: item.Id
                            }
                        })
                    };
                },
                cache: true
            },
            dropdownCssClass: "custom-dropdown-scroll"
        });
    
</script>
<script>
        $('.Inventario3').select2({
            theme: "bootstrap4",
            language: {
                noResults: function() {
                    return "Producto no encontrado";
                },
                searching: function() {
                    return "Buscando......."
                }
            },
            ajax: {
                url: "{{ url('/Ajax/Inventario3') }}",
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.Codigo + " | " + item.Nombre,
                                id: item.Id
                            }
                        })
                    };
                },
                cache: true
            },
            dropdownCssClass: "custom-dropdown-scroll"
        });
    
</script>
<script>
        $('.Proveedor').select2({
            theme: "bootstrap4",
            language: {
                noResults: function() {
                    return "Proveedor no encontrado";
                },
                searching: function() {
                    return "Buscando......."
                }
            },
            ajax: {
                url: "{{ url('/Ajax/Proveedor') }}",
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: 'NIT: ' + item.Nit + ' | PROVEEDOR: ' + item.Nombre,
                                id: item.Id
                            }
                        })
                    };
                },
                cache: true
            }
        });
    
</script>
<script>
        $(document).ready(function() {
            //obtenemos el valor de los input
            var a = 1; //contador para asignar id al boton que borrara la fila

            $('#adicionar1').click(function() {
                var nombre = document.getElementById("nombre").value;
                var Inventario = document.getElementById("Inventario1").value;
                var Cantidad = document.getElementById("Cantidad").value;
                var Valor = document.getElementById("Valor").value;
                var nuevaCategoria = $("select option:selected").text();


                if (Inventario == null || Inventario.length == 0 || isNaN(Inventario)) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Debe ingresar un producto'
                    })
                    return false;
                }

                if (Cantidad == null || Cantidad.length == 0 || isNaN(Cantidad)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Debe ingresar una Cantidad'
                    })
                    return false;
                }


                var fila = '<tr id="row' + a + '"><td>' +
                    '<input type="hidden" name="IdInventario[]" value="' + Inventario + '">' +
                    '<span>' + nuevaCategoria + '</span>' +
                    '<input type="hidden" name="Inventario" value="' + nuevaCategoria + '">' +
                    '</td><td class="num"><input type="number" class="form-control form-control-sm" name="Cantidad[]" value="' + Cantidad + '"></td>' +
                    '<td class="num"><input type="number" step="any" class="form-control form-control-sm" name="Valor[]" value="' + Valor + '"></td>' +
                    '<td class="text-center"><button type="button" name="remove" id="' + a + '" class="btn btn-outline-danger btn-sm btn_remove">Quitar</button></td></tr>';
                a = a + 1;

                $('#mytable tbody').append(fila);
                $("#adicionados").text(
                    ""); //esta instruccion limpia el div adicionamos para que no se vayan acumulando
                var nFilas = $("#mytable tr").length;
                $("#adicionados").append(nFilas - 1);
                //le resto 1 para no contar la fila del header
                document.getElementById("Inventario1").value = "";
                document.getElementById("Cantidad").value = "";
                document.getElementById("nombre").value = "";
                document.getElementById("Valor").value = "";
                document.getElementById("nombre").focus();
            });
            $(document).on('click', '.btn_remove', function() {
                var button_id = $(this).attr("id");
                //cuando da click obtenemos el id del boton
                $('#row' + button_id + '').remove(); //borra la fila
                //limpia el para que vuelva a contar las filas de la tabla
                $("#adicionados").text("");
                var nFilas = $("#mytable tr").length;
                $("#adicionados").append(nFilas - 1);
            });
        });
    
</script>
<script>
        $(document).ready(function() {
            //obtenemos el valor de los input
            var a = 1; //contador para asignar id al boton que borrara la fila
            $('#adicionar2').click(function() {
                var nombre = document.getElementById("nombre").value;
                var Inventario = document.getElementById("Inventario2").value;
                var Cantidad = document.getElementById("Cantidad").value;
                var Valor = document.getElementById("Valor").value;
                var nuevaCategoria = $("select option:selected").text();



                if (Inventario == null || Inventario.length == 0 || isNaN(Inventario)) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Debe ingresar un producto'
                    })
                    return false;
                }

                if (Cantidad == null || Cantidad.length == 0 || isNaN(Cantidad)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Debe ingresar una Cantidad',

                    })
                    return false;
                }


                var fila = '<tr id="row' + a + '"><td>' +
                    '<input type="hidden" name="IdInventario[]" value="' + Inventario + '">' +
                    '<span>' + nuevaCategoria + '</span>' +
                    '<input type="hidden" name="Inventario" value="' + nuevaCategoria + '">' +
                    '</td><td class="num"><input type="number" class="form-control form-control-sm" name="Cantidad[]" value="' + Cantidad + '"></td>' +
                    '<td class="num"><input type="number" step="any" class="form-control form-control-sm" name="Valor[]" value="' + Valor + '"></td>' +
                    '<td class="text-center"><button type="button" name="remove" id="' + a + '" class="btn btn-outline-danger btn-sm btn_remove">Quitar</button></td></tr>';
                a = a + 1;

                $('#mytable tbody').append(fila);
                $("#adicionados").text(
                    ""); //esta instruccion limpia el div adicionamos para que no se vayan acumulando
                var nFilas = $("#mytable tr").length;
                $("#adicionados").append(nFilas - 1);
                //le resto 1 para no contar la fila del header
                document.getElementById("Inventario2").value = "";
                document.getElementById("Cantidad").value = "";
                document.getElementById("nombre").value = "";
                document.getElementById("Valor").value = "";
                document.getElementById("nombre").focus();
            });
            $(document).on('click', '.btn_remove', function() {
                var button_id = $(this).attr("id");
                //cuando da click obtenemos el id del boton
                $('#row' + button_id + '').remove(); //borra la fila
                //limpia el para que vuelva a contar las filas de la tabla
                $("#adicionados").text("");
                var nFilas = $("#mytable tr").length;
                $("#adicionados").append(nFilas - 1);
            });
        });
    
</script>
<script>
        $(document).ready(function() {
            //obtenemos el valor de los input
            var a = 1; //contador para asignar id al boton que borrara la fila
            $('#adicionar3').click(function() {
                var nombre = document.getElementById("nombre").value;
                var Inventario = document.getElementById("Inventario3").value;
                var Cantidad = document.getElementById("Cantidad").value;
                var Valor = document.getElementById("Valor").value;
                var nuevaCategoria = $("select option:selected").text();

                if (Inventario == null || Inventario.length == 0 || isNaN(Inventario)) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Debe ingresar un producto'
                    })
                    return false;
                }

                if (Cantidad == null || Cantidad.length == 0 || isNaN(Cantidad)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Debe ingresar una Cantidad'
                    })
                    return false;
                }

                var fila = '<tr id="row' + a + '"><td>' +
                    '<input type="hidden" name="IdInventario[]" value="' + Inventario + '">' +
                    '<span>' + nuevaCategoria + '</span>' +
                    '<input type="hidden" name="Inventario" value="' + nuevaCategoria + '">' +
                    '</td><td class="num"><input type="number" class="form-control form-control-sm" name="Cantidad[]" value="' + Cantidad + '"></td>' +
                    '<td class="num"><input type="number" step="any" class="form-control form-control-sm" name="Valor[]" value="' + Valor + '"></td>' +
                    '<td class="text-center"><button type="button" name="remove" id="' + a + '" class="btn btn-outline-danger btn-sm btn_remove">Quitar</button></td></tr>';
                a = a + 1;

                $('#mytable tbody').append(fila);
                $("#adicionados").text(
                    ""); //esta instruccion limpia el div adicionamos para que no se vayan acumulando
                var nFilas = $("#mytable tr").length;
                $("#adicionados").append(nFilas - 1);
                //le resto 1 para no contar la fila del header
                document.getElementById("Inventario3").value = "";
                document.getElementById("Cantidad").value = "";
                document.getElementById("nombre").value = "";
                document.getElementById("Valor").value = "";
                document.getElementById("nombre").focus();
            });
            $(document).on('click', '.btn_remove', function() {
                var button_id = $(this).attr("id");
                //cuando da click obtenemos el id del boton
                $('#row' + button_id + '').remove(); //borra la fila
                //limpia el para que vuelva a contar las filas de la tabla
                $("#adicionados").text("");
                var nFilas = $("#mytable tr").length;
                $("#adicionados").append(nFilas - 1);
            });
        });
    
</script>
<script>
    // Sin productos el servidor redirige a una ruta solo POST; se avisa antes de enviar.
    $(function () {
        $('#formCrearFactura').on('submit', function (e) {
            if ($('input[name="IdInventario[]"]').length === 0) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Agregue al menos un producto' });
            }
        });
    });
</script>
@endpush

@section('content')
@php
    $f = collect($Factura)->first() ?? [];
    $nombreProveedor = isset($f['Proveedor'])
        ? \Illuminate\Support\Facades\DB::table('Proveedor')->where('Id', $f['Proveedor'])->value('Nombre')
        : null;
@endphp

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Productos recibidos') }}</h1>
        <p class="inv-subtitulo">Agregue cada producto que llegó en esta remisión y luego guarde el pedido.</p>
    </div>
</div>

@include('Inventario.partials.pasos', ['pasos' => ['Datos de la remisión', 'Productos recibidos'], 'actual' => 2])

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($f)
    <div class="inv-resumen">
        <div class="inv-dato"><small>Proveedor</small><strong>{{ $nombreProveedor ?? '—' }}</strong></div>
        <div class="inv-dato"><small>Remisión</small><strong>{{ $f['NFactura'] ?? '—' }}</strong></div>
        <div class="inv-dato"><small>Orden de pedido</small><strong>{{ $f['OPedido'] ?? '—' }}</strong></div>
        <div class="inv-dato"><small>Fecha de ingreso</small><strong>{{ $f['FechaI'] ?? '—' }}</strong></div>
        <div class="inv-dato"><small>Valor de la remisión</small><strong>${{ number_format((float) ($f['PrecioT'] ?? 0), 0, ',', '.') }}</strong></div>
    </div>
@endif

<div class="row">
    <div class="col-lg-5 mb-3">
        <div class="inv-tarjeta h-100">
            @foreach ($Factura as $item)
                @if (in_array((int) $item['Division'], [1, 2, 3], true))
                    <input id="nombre" type="hidden">
                    <div class="inv-seccion"><i class="fas fa-plus-circle"></i> Agregar producto</div>
                    <div class="form-group">
                        <label for="Inventario{{ $item['Division'] }}">{{ __('Producto') }} <span class="inv-campo-obligatorio">*</span></label>
                        <select class="Inventario{{ $item['Division'] }} w-100" required name="Inventario{{ $item['Division'] }}"
                                id="Inventario{{ $item['Division'] }}" autocomplete="off"></select>
                        <small class="inv-ayuda">Busque por código o nombre.</small>
                    </div>
                    <div class="row">
                        <div class="form-group col-6">
                            <label for="Cantidad">{{ __('Cantidad') }} <span class="inv-campo-obligatorio">*</span></label>
                            <input type="number" id="Cantidad" class="form-control" min="0" step="any" autocomplete="off">
                        </div>
                        <div class="form-group col-6">
                            <label for="Valor">{{ __('Valor unitario') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                <input type="number" id="Valor" class="form-control" min="0" step="any" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <button id="adicionar{{ $item['Division'] }}" class="btn btn-primary btn-block" type="button">
                        <i class="fas fa-plus mr-1"></i> Agregar a la lista
                    </button>
                @else
                    <div class="inv-vacio"><i class="fas fa-exclamation-circle"></i><p>La división seleccionada no tiene inventario configurado.</p></div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="col-lg-7 mb-3">
        <form method="POST" action="{{ route('Crear_Factura') }}" id="formCrearFactura" class="inv-tarjeta h-100 d-flex flex-column">
            @csrf
            @foreach ($Factura as $item)
                <input type="hidden" name="Proveedor" value="{{ $item['Proveedor'] }}">
                <input type="hidden" name="NFactura" value="{{ $item['NFactura'] }}">
                <input type="hidden" name="PrecioT" value="{{ $item['PrecioT'] }}">
                <input type="hidden" name="OPedido" value="{{ $item['OPedido'] }}">
                <input type="hidden" name="FechaI" value="{{ $item['FechaI'] }}">
                <input type="hidden" name="Division" value="{{ $item['Division'] }}">
            @endforeach

            <div class="inv-seccion d-flex justify-content-between">
                <span><i class="fas fa-list"></i> Productos del pedido</span>
                <span class="badge badge-info" style="font-size:.8rem"><span id="adicionados">0</span> productos</span>
            </div>

            <div class="table-responsive">
                <table id="mytable" class="table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Producto</th>
                            <th scope="col" class="num" style="width:120px">Cantidad</th>
                            <th scope="col" class="num" style="width:150px">Valor unitario</th>
                            <th scope="col" class="text-center" style="width:90px"><span class="sr-only">Quitar</span></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="listaVacia" class="inv-vacio">
                <i class="fas fa-box-open"></i>
                <p>Todavía no ha agregado productos.<br>Use el formulario de la izquierda.</p>
            </div>

            <div class="mt-auto pt-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center p-3 mb-3" style="background:#F8FAFC;border-radius:12px;gap:.5rem">
                    <span class="inv-texto-2">Total de lo agregado</span>
                    <span>
                        <strong id="totalAgregado" style="font-size:1.15rem">$0</strong>
                        @if (! empty($f['PrecioT']))
                            <small class="inv-texto-2"> de ${{ number_format((float) $f['PrecioT'], 0, ',', '.') }}</small>
                        @endif
                    </span>
                </div>
                <div class="inv-acciones inv-acciones--fin">
                    <a class="btn btn-outline-secondary" href="{{ route('EntAlmacen') }}">{{ __('Cancelar') }}</a>
                    <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save mr-1"></i> {{ __('Guardar pedido') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    // Muestra el mensaje de lista vacía y calcula el total (cantidad × valor) cada vez que cambia la tabla.
    $(function () {
        var cuerpo = document.querySelector('#mytable tbody');
        var formato = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 });

        function actualizar() {
            var filas = cuerpo.querySelectorAll('tr');
            var total = 0;
            filas.forEach(function (fila) {
                var cant = parseFloat((fila.querySelector('input[name="Cantidad[]"]') || {}).value) || 0;
                var valor = parseFloat((fila.querySelector('input[name="Valor[]"]') || {}).value) || 0;
                total += cant * valor;
            });
            document.getElementById('totalAgregado').textContent = '$' + formato.format(total);
            document.getElementById('listaVacia').style.display = filas.length ? 'none' : '';
            document.querySelector('#mytable').closest('.table-responsive').style.display = filas.length ? '' : 'none';
        }

        new MutationObserver(actualizar).observe(cuerpo, { childList: true });
        $(cuerpo).on('input', 'input', actualizar);
        actualizar();
    });
</script>
@endpush
@endsection
