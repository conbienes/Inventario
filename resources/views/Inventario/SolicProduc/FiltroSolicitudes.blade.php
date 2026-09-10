@extends("theme.$theme.layout")

@section('content')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <!-- Formulario -->
    <form method="POST" action="{{ route('TotalAprobadasP') }}">
        @csrf
        <div class="card-body">
            <!-- Fecha Inicial -->
            <div class="form-group">
                <label for="FechaInicial">{{ __('Fecha Inicial') }}</label>
                <input type="date" class="form-control" name="FechaInicial" id="FechaInicial" autocomplete="off" autofocus required>
            </div>

            <!-- Fecha Final -->
            <div class="form-group">
                <label for="FechaFinal">{{ __('Fecha Final') }}</label>
                <input type="date" class="form-control" name="FechaFinal" id="FechaFinal" autocomplete="off" autofocus required>
            </div>

            <!-- Empleado -->
            <div class="form-group">
                <label for="Empleados">{{ __('Empleado') }}</label>
                <select class="Empleados form-control" style="width: 100%" name="Empleados" id="Empleados"
                    autocomplete="off">
                </select>
            </div>

            <!-- Botones de acción -->
            <div class="form-group text-center mt-4">
                <button type="submit" class="btn btn-success mr-2">{{ __('Filtrar') }}</button>
                <a class="btn btn-danger" href="{{ route('TotalAprobadas') }}">{{ __('Cancelar') }}</a>
            </div>
        </div>
    </form>


    <script type="text/javascript">
        $('.Empleados').select2({

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
                url: '/Ajax/Empleados',
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.nombre + " " + item.apellidos,
                                id: item.id
                            }
                        })
                    };
                },
                cache: true
            }
        });
    </script>

@endsection
