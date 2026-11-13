@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/lte/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@push('js')
    <script src="{{ asset('assets/lte/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function () {
            $('.Empleados').select2({
                theme: 'bootstrap4',
                placeholder: 'Todos los empleados',
                allowClear: true,
                language: {
                    noResults: function () { return 'Empleado no encontrado'; },
                    searching: function () { return 'Buscando...'; },
                    inputTooShort: function () { return 'Escriba al menos 2 letras del nombre'; }
                },
                minimumInputLength: 2,
                ajax: {
                    url: '{{ url('/Ajax/Empleados') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { q: params.term }; },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return { text: item.nombre + ' ' + item.apellidos, id: item.id };
                            })
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
@endpush

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Filtrar solicitudes aprobadas</h1>
        <p class="inv-subtitulo">Indique el rango de fechas y, opcionalmente, un empleado.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('TotalAprobadas') }}"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="inv-tarjeta-wrap">
    <form method="POST" action="{{ route('TotalAprobadasP') }}" class="inv-tarjeta p-3 p-md-4">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="form-group col-md-4">
                <label for="FechaInicial">Fecha inicial <span class="inv-campo-obligatorio">*</span></label>
                <input type="date" class="form-control" name="FechaInicial" id="FechaInicial" autocomplete="off" required>
            </div>

            <div class="form-group col-md-4">
                <label for="FechaFinal">Fecha final <span class="inv-campo-obligatorio">*</span></label>
                <input type="date" class="form-control" name="FechaFinal" id="FechaFinal" autocomplete="off" required>
            </div>

            <div class="form-group col-md-4">
                <label for="Empleados">Empleado <span class="inv-ayuda">(opcional)</span></label>
                <select class="Empleados form-control" name="Empleados" id="Empleados" autocomplete="off"></select>
            </div>
        </div>

        <div class="inv-acciones inv-acciones--fin mt-2">
            <a class="btn btn-outline-secondary" href="{{ route('TotalAprobadas') }}">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Aplicar filtro</button>
        </div>
    </form>
</div>
@endsection
