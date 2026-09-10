@extends("theme.$theme.layout")

@section('content')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.css">

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ __('SOLICITAR PRODUCTO') }}</h3>
        </div>
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
                                @foreach ($Empleado as $item)
                                    <div class="form-group">
                                        <label for="Nombre">{{ __('Solicitante') }}</label>
                                        <input type="text" readonly="readonly" class="form-control" name="Solicitante"
                                            autocomplete="off" autofocus value="{{ $item->nombre }} {{ $item->apellidos }}">
                                    </div>
                                    <input id="nombre" type="hidden" class="form-control" type="text"
                                        value={{ $item->id }}>
                                    <div class="form-group">
                                        <input type="hidden" required class="form-control" name="id_area"
                                            value={{ $item->id_area }}>
                                    </div>
                                @endforeach

                                @foreach ($Area as $item)
                                    <div class="form-group">
                                        <label for="Area">{{ __('Area Solicitante') }}</label>
                                        <input type="text" readonly="readonly" class="form-control" name="Area"
                                            autocomplete="off" value="{{ $item->nombre }}">
                                    </div>
                                @endforeach

                                <label>Producto</label>
                                <select class="Inventario" style="width: 100%" required name="Inventario" id="Inventario"
                                    autocomplete="off"></select>
                                <br><br>
                                <label>Cantidad</label><br>
                                <input type="number" id="Cantidad" class="form-control" type="text"
                                    autocomplete="off"><br>

                                <button id="adicionar" class="btn btn-success" type="button">+ Agregar
                                    Producto</button>
                            </div>
                        </td>
                    </tr>
        </form>

        </table>
        <form method="POST" action="{{ route('actualizar_SolicProduc') }}">
            @csrf
            <table>
                <tr>
                    <td>
                        <div class="form-group">
                            <label for="Fecha">{{ __('Fecha Tentativa') }}</label>
                            <input type="date" class="form-control" name="Fecha" autocomplete="off" required>
                        </div>
                    </td>
                    <td width="50">
                        <br>
                    </td>
                    <td width="500" ALIGN="center">
                        <div class="form-group">
                            <label>Observaciones</label><br>
                            <textarea name="Observaciones" rows="2" cols="50" name="Observaciones" id="Observaciones"></textarea>
                        </div>
                    </td>
                </tr>
            </table>
            @foreach ($Empleado as $item)
                <input name="IdSolicitante" type="hidden" class="form-control" type="text" value={{ $item->id }}>
                <input type="hidden" required class="form-control" name="id_area" value={{ $item->id_area }}>
            @endforeach
            <label> </label>
            <strong> Elementos en la Tabla:</strong>

            <div id="adicionados"></div>


            <table id="mytable" class="table table-striped table-bordered">
                <tr>
                    <th width="500">Nombre</th>
                    <th width="50">Cantidad</th>
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
                            <a class="btn btn-danger" href="{{ route('InicioInventario') }}">{{ __('Cancelar') }}
                            </a>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    </div>



    <script>
        $('#Inventario').focus();
        $('#Inventario').prop('disabled', false);


        $('.Inventario').select2({
            theme: "classic",
            language: {
                noResults: function() {
                    return "Producto no encontrado";
                },
                searching: function() {
                    return "Buscando......."
                }
            },
            ajax: {
                url: '/Ajax/Inventario',
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    console.log(data);
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.Nombre + " | Cant: " + item.Cantidad,
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
        var i = 1; //contador para asignar id al boton que borrara la fila

        $(document).ready(function() {
            //obtenemos el valor de los input

            $('#adicionar').click(function() {
                var nombre = document.getElementById("nombre").value;
                var Inventario = document.getElementById("Inventario").value;
                var Cantidad = document.getElementById("Cantidad").value;
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




                var fila = '<tr id="row' + i + '"><input type="hidden"  name="IdInventario[]" value="' +
                    Inventario + '" ><td> <label> <font size=2> ' + nuevaCategoria +
                    '</label><input type="hidden"  name="Inventario" value="' + nuevaCategoria +
                    '" ></td><td><input type="number"  style="width:50" name="Cantidad[]" value="' +
                    Cantidad +
                    '" ></td><td><button type="button" name="btn_remove" id="' + i +
                    '" class="btn btn-danger btn_remove">Quitar</button></td></tr>';
                i++;

                $('#mytable tr:first').after(fila);
                $("#adicionados").text(
                    ""); //esta instruccion limpia el div adicionamos para que no se vayan acumulando
                var nFilas = $("#mytable tr").length;
                $("#adicionados").append(nFilas - 1);
                //le resto 1 para no contar la fila del header
                document.getElementById("Inventario").value = "";
                document.getElementById("Cantidad").value = "";
                document.getElementById("nombre").value = "";
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
    .select2-search__field {
        user-select: text;
    }


    .select2-container--material {
        width: 100% !important;
        font-size: 80%
    }

    .select2-container--material .select2-selection--single {
        background-color: transparent;
        border: none;
        border-bottom: 0.5px solid #ced4da;
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
        border-bottom: 0.5px solid #ced4da;
        border-radius: 0;
        outline: none;
    }

    .select2-container--material .select2-results__option--highlighted[aria-selected] {
        background-color: #3498DB;
        color: rgb(0, 0, 0);
    }
</style>
