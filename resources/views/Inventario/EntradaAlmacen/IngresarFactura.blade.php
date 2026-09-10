@extends("theme.$theme.layout")

@section('content')

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <link rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.css">

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ __('REGISTRAR PRODUCTOS ALMACEN') }}</h3>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form>
                    <div class="card-body">
                        <table>
                            <tr>
                                <td width="500">
                                    @csrf
                                    <div class="form-group">
                                        <label>Producto</label>
                                        @foreach ($Factura as $item)
                                            <input id="nombre" type="hidden" class="form-control" type="text">
                                            @if ($item['Division'] == 1)
                                                <select class="Inventario1" required name="Inventario1" id="Inventario1"
                                                    autocomplete="off"></select> <br><br>
                                                <label>Cantidad</label><br>
                                                <input type="number" id="Cantidad" class="form-control" type="text"
                                                    autocomplete="off"><br>
                                                <label>Valor Unidad</label><br>
                                                <input type="number" id="Valor" class="form-control" type="text"
                                                    autocomplete="off"><br>
                                                <button id="adicionar1" class="btn btn-success" type="button">+ Agregar
                                                    Producto</button>
                                            @endif

                                            @if ($item['Division'] == 2)
                                                <select class="Inventario2" required name="Inventario2" id="Inventario2"
                                                    autocomplete="off"></select> <br><br>
                                                <label>Cantidad</label><br>
                                                <input type="number" id="Cantidad" class="form-control" type="text"
                                                    autocomplete="off"><br>
                                                <label>Valor Unidad</label><br>
                                                <input type="number" id="Valor" class="form-control" type="text"
                                                    autocomplete="off"><br>
                                                <button id="adicionar2" class="btn btn-success" type="button">+ Agregar
                                                    Producto</button>
                                            @endif

                                            @if ($item['Division'] == 3)
                                                <select class="Inventario3" required name="Inventario3" id="Inventario3"
                                                    autocomplete="off"></select> <br><br>
                                                <label>Cantidad</label><br>
                                                <input type="number" id="Cantidad" class="form-control" type="text"
                                                    autocomplete="off"><br>
                                                <label>Valor Unidad</label><br>
                                                <input type="number" id="Valor" class="form-control" type="text"
                                                    autocomplete="off"><br>
                                                <button id="adicionar3" class="btn btn-success" type="button">+ Agregar
                                                    Producto</button>
                                            @else
                                            @endif
                                        @endforeach

                                    </div>
                                </td>
                            </tr>

                        </table>
                </form>

                <form method="POST" action="{{ route('Crear_Factura') }}">
                    @csrf
                    @foreach ($Factura as $item)
                        <input id="Proveedor" name="Proveedor" type="hidden" class="form-control" type="text"
                            value={{ $item['Proveedor'] }}>
                        <input id="NFactura" name="NFactura" type="hidden" class="form-control" type="text"
                            value={{ $item['NFactura'] }}>
                        <input id="PrecioT" name="PrecioT" type="hidden" class="form-control" type="text"
                            value={{ $item['PrecioT'] }}>
                        <input id="OPedido" name="OPedido" type="hidden" class="form-control" type="text"
                            value={{ $item['OPedido'] }}>
                        <input id="FechaI" name="FechaI" type="hidden" class="form-control" type="text"
                            value={{ $item['FechaI'] }}>
                        <input id="Division" name="Division" type="hidden" class="form-control" type="text"
                            value={{ $item['Division'] }}>
                    @endforeach
                    <strong> Elementos en la Tabla:</strong>

                    <div id="adicionados"></div>

                    <table id="mytable" class="table table-striped table-bordered">
                        <tr>
                            <th width="400">Nombre</th>
                            <th width="50">Cantidad</th>
                            <th width="100">Valor Unidad</th>
                            <th width="100">Eliminar</th>
                        </tr>
                    </table>
                    <table class="table">
                        <tr ALIGN="center">
                            <td>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">{{ __('Crear') }}</button>
                                </div>
                            </td>
                            <td>
                                <div class="form-group ">
                                    <a class="btn btn-danger" href="{{ route('EntAlmacen') }}">{{ __('Cancelar') }} </a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script type="text/javascript">
        $('.Inventario1').select2({
            theme: "material",
            language: {
                noResults: function() {
                    return "Producto no encontrado";
                },
                searching: function() {
                    return "Buscando.......";
                }
            },
            ajax: {
                url: '/Ajax/Inventario1',
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

    <script type="text/javascript">
        $('.Inventario2').select2({
            theme: "material",
            language: {
                noResults: function() {
                    return "Producto no encontrado";
                },
                searching: function() {
                    return "Buscando......."
                }
            },
            ajax: {
                url: '/Ajax/Inventario2',
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
    <script type="text/javascript">
        $('.Inventario3').select2({
            theme: "material",
            language: {
                noResults: function() {
                    return "Producto no encontrado";
                },
                searching: function() {
                    return "Buscando......."
                }
            },
            ajax: {
                url: '/Ajax/Inventario3',
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

    <script type="text/javascript">
        $('.Proveedor').select2({
            theme: "material",
            language: {
                noResults: function() {
                    return "Proveedor no encontrado";
                },
                searching: function() {
                    return "Buscando......."
                }
            },
            ajax: {
                url: '/Ajax/Proveedor',
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


                var fila = '<tr id="row' + a + '"><input type="hidden"  name="IdInventario[]" value="' +
                    Inventario + '" ><td> <label> <font size=2> ' +
                    nuevaCategoria + '</label><input type="hidden"  name="Inventario" value="' +
                    nuevaCategoria +
                    '" ></td><td><input type="number"  style="width:50" name="Cantidad[]" value="' +
                    Cantidad +
                    '" ></td><td><input type="Valor"  style="width:100" name="Valor[]" value="' + Valor +
                    '" ></td><td><button type="button" name="remove" id="' + a +
                    '" class="btn btn-danger btn_remove">Quitar</button></td></tr>';
                a = a + 1;

                $('#mytable tr:first').after(fila);
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


                var fila = '<tr id="row' + a + '"><input type="hidden"  name="IdInventario[]" value="' +
                    Inventario + '" ><td> <label> <font size=2> ' +
                    nuevaCategoria + '</label><input type="hidden"  name="Inventario" value="' +
                    nuevaCategoria +
                    '" ></td><td><input type="number"  style="width:50" name="Cantidad[]" value="' +
                    Cantidad +
                    '" ></td><td><input type="Valor"  style="width:100" name="Valor[]" value="' + Valor +
                    '" ></td><td><button type="button" name="remove" id="' + a +
                    '" class="btn btn-danger btn_remove">Quitar</button></td></tr>';
                a = a + 1;

                $('#mytable tr:first').after(fila);
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

                var fila = '<tr id="row' + a + '"><input type="hidden"  name="IdInventario[]" value="' +
                    Inventario + '" ><td> <label> <font size=2> ' +
                    nuevaCategoria + '</label><input type="hidden"  name="Inventario" value="' +
                    nuevaCategoria +
                    '" ></td><td><input type="number"  style="width:50" name="Cantidad[]" value="' +
                    Cantidad +
                    '" ></td><td><input type="Valor"  style="width:100" name="Valor[]" value="' + Valor +
                    '" ></td><td><button type="button" name="remove" id="' + a +
                    '" class="btn btn-danger btn_remove">Quitar</button></td></tr>';
                a = a + 1;

                $('#mytable tr:first').after(fila);
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

@endsection


<style>
    .select2-container--material {
        width: 100% !important;
        font-size: 80%
    }

    .select2-container--material .select2-selection--single {
        background-color: transparent;
        border: none;
        border-bottom: 1px solid #ced4da;
        border-radius: 0;
        box-shadow: none;
        box-sizing: content-box;
        height: auto;
        margin: 0;
        outline: none;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .select2-container--material .select2-selection--single .select2-selection__placeholder {
        color: #999;
    }

    .select2-container--material .select2-search--dropdown .select2-search__field {
        border: none;
        border-bottom: 1px solid #ced4da;
        border-radius: 0;
        outline: none;
    }

    .select2-container--material .select2-results__option--highlighted[aria-selected] {
        background-color: #3498DB;
        color: rgb(0, 0, 0);
    }

    .custom-dropdown-scroll {
        max-height: 300px;
        /* Altura máxima del dropdown */
        overflow-y: auto;
        /* Habilitar scroll vertical */
        overflow-x: hidden;
        /* Evitar scroll horizontal */
    }
</style>
