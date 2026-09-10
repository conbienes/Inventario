@extends("theme.$theme.layout")

@section('content')

    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/sweetalert2@9.17.2/dist/sweetalert2.min.css">


    <!-- Modal -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ __('CREAR') }}</h3>
        </div>

        <div class="modal-content ">
            <div class="modal-content">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('Guardar_Inventario') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="Nombre">{{ __('Division') }}</label><BR>
                            <select class="Division" style="width: 100%" name="Division" id="Division"
                                value="pureba"></select>
                        </div>
                        <div class="form-group">
                            <label for="Almacen">{{ __('Almacen') }}</label><br>
                            <select class="Almacen" style="width: 100%" name="Almacen" id="Almacen"></select>
                        </div>
                        <div class="form-group">
                            <label for="Nombre">{{ __('Categoria') }}</label><br>
                            <select class="Categoria" style="width: 100%" name="Categoria" id="Categoria"></select>
                        </div>
                        <div class="form-group">
                            <label for="Nombre">{{ __('Producto') }}</label><br>
                            <select class="Producto " name="Producto" style="width: 100%" id="Producto">
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Estanteria">{{ __('Estanteria') }}</label>
                            <input type="text" class="form-control" name="Estanteria" autocomplete="off" autofocus
                                required>
                        </div>
                        <div class="form-group">
                            <label for="Nombre">{{ __('Entrepaño') }}</label>
                            <input type="Entrepaño" class="form-control" name="Entrepaño" autocomplete="off" autofocus
                                required>
                        </div>
                        <div class="form-group">
                            <label for="Gaveta">{{ __('Gaveta') }}</label>
                            <input type="text" class="form-control" name="Gaveta" autocomplete="off" autofocus required>
                        </div>
                        <div class="form-group">
                            <label for="Nombre">{{ __('Cantidades Inventario') }}</label>
                            <input type="text" class="form-control" name="Cantidades" autocomplete="off" autofocus
                                required>
                        </div>
                        <div class="form-group">
                            <label for="Nombre">{{ __('Cantidad Minima') }}</label>
                            <input type="text" class="form-control" name="CantidadMax" autocomplete="off" autofocus
                                required>
                        </div>
                        <div class="form-group">
                            <label for="Nombre">{{ __('Cantidad Maxima') }}</label>
                            <input type="text" class="form-control" name="CantidadMin" autocomplete="off" autofocus
                                required>
                        </div>
                        <table class="table">
                            <tr ALIGN="center">
                                <td>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success">{{ __('Crear') }}</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group ">
                                        <a class="btn btn-danger" href="javascript: history.go(-1)">{{ __('Cancelar') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script type="text/javascript">
        $('.Division').select2({
            theme: "classic",
            language: {
                noResults: function() {
                    return "Division no encontrada";
                },
                searching: function() {
                    return "Buscando......."
                }
            },

            ajax: {
                url: '/Ajax/Division',
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.Nombre,
                                id: item.Id
                            }
                        })
                    };
                },
                cache: true
            }
        });
    </script>

    <script type="text/javascript">
        $('.Almacen').select2({
            theme: "classic",
            language: {
                noResults: function() {
                    return "Almacen no encontrado";
                },
                searching: function() {
                    return "Buscando......."
                }
            },
            ajax: {
                url: '/Ajax/Almacen',
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.Nombre,
                                id: item.Id
                            }
                        })
                    };
                },
                cache: true
            }
        });
    </script>


    <script type="text/javascript">
        $('#Producto').focus();
        $('#Producto').prop('disabled', false);

        $('.Producto').select2({
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
                url: '/Ajax/Producto',
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.Nombre,
                                id: item.Id
                            }
                        })
                    };
                },
                cache: true
            }
        });
    </script>

    <script type="text/javascript">
        $('.Categoria').select2({
            theme: "classic",
            language: {
                noResults: function() {
                    return "Categoria no encontrada";
                },
                searching: function() {
                    return "Buscando......."
                }
            },
            ajax: {
                url: '/Ajax/Categoria',
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.Nombre,
                                id: item.Id
                            }
                        })
                    };
                },
                cache: true
            }
        });
    </script>

@endsection

<style>
       .modal-content {
        width: 80%; /* Ajusta este valor según sea necesario */
        max-width: 600px; /* Limita el ancho máximo si es necesario */
        margin: auto; /* Centra el modal */
    }
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
