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
    $('#Proveedor').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Busque por NIT o nombre',
        language: {
            noResults: function () { return 'Proveedor no encontrado'; },
            searching: function () { return 'Buscando...'; }
        },
        ajax: {
            url: "{{ url('/Ajax/Proveedor') }}",
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: 'NIT: ' + item.Nit + ' | PROVEEDOR: ' + item.Nombre,
                            id: item.Id
                        };
                    })
                };
            },
            cache: true
        }
    });
</script>
@endpush

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Ingreso de pedido de compra') }}</h1>
        <p class="inv-subtitulo">Registre los datos de la remisión. En el siguiente paso podrá añadir los productos.</p>
    </div>
</div>

@include('Inventario.partials.pasos', ['pasos' => ['Datos de la remisión', 'Productos recibidos'], 'actual' => 1])

<div class="inv-tarjeta-wrap">
    <form method="POST" action="{{ route('AñadirFactura') }}" class="inv-tarjeta p-3 p-md-4">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Revise los datos:</strong>
                <ul class="mb-0 pl-3 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @foreach ($Division as $item)
            <input type="hidden" name="Division" value="{{ $item->id }}">
        @endforeach

        <div class="inv-seccion"><i class="fas fa-user"></i> Quién recibe</div>
        <div class="row">
            @foreach ($Empleado as $item)
                <div class="col-md-6 mb-3">
                    <label for="Solicitante">{{ __('Almacenista') }}</label>
                    <input type="text" id="Solicitante" class="form-control" name="Solicitante" readonly
                           value="{{ $item->nombre }} {{ $item->apellidos }}">
                </div>
            @endforeach
        </div>

        <div class="inv-seccion"><i class="fas fa-truck"></i> Remisión del proveedor</div>
        <div class="row">
            <div class="col-12 mb-3">
                <label for="Proveedor">{{ __('Proveedor') }} <span class="inv-campo-obligatorio">*</span></label>
                <select id="Proveedor" name="Proveedor" class="form-control" required></select>
                <small class="form-text">Busque por NIT o por nombre del proveedor.</small>
            </div>
            <div class="col-md-6 mb-3">
                <label for="NFactura">{{ __('# Remisión') }} <span class="inv-campo-obligatorio">*</span></label>
                <input type="text" id="NFactura" class="form-control" name="NFactura" autocomplete="off" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="OPedido">{{ __('# Orden de pedido') }} <span class="inv-campo-obligatorio">*</span></label>
                <input type="text" id="OPedido" class="form-control" name="OPedido" autocomplete="off" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="PrecioT">{{ __('Precio total') }} <span class="inv-campo-obligatorio">*</span></label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                    <input type="number" id="PrecioT" class="form-control" name="PrecioT" min="0" step="any" autocomplete="off" required>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="FechaI">{{ __('Fecha de ingreso') }} <span class="inv-campo-obligatorio">*</span></label>
                <input type="date" id="FechaI" class="form-control" name="FechaI" required>
            </div>
        </div>

        <p class="inv-ayuda mb-0"><span class="inv-campo-obligatorio">*</span> Campos obligatorios.</p>

        <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 pt-3" style="border-top:1px solid #E2E8F0">
            @foreach ($Division as $item)
                <a class="btn btn-outline-secondary" href="{{ route('Vista_EntAlmacen', ['id' => Crypt::encrypt($item->id)]) }}"><i class="fas fa-times mr-1"></i> {{ __('Cancelar') }}</a>
            @endforeach
            <button type="submit" class="btn btn-success btn-lg">{{ __('Continuar con los productos') }} <i class="fas fa-arrow-right ml-1"></i></button>
        </div>
    </form>
</div>
@endsection
