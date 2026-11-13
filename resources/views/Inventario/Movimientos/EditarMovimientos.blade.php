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
    function movimientoSelect(selector, endpoint, etiqueta, textoFn) {
        $(selector).select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Escriba para buscar ' + etiqueta.toLowerCase(),
            language: {
                noResults: function () { return etiqueta + ' no encontrado'; },
                searching: function () { return 'Buscando...'; }
            },
            ajax: {
                url: endpoint,
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return { text: textoFn(item), id: item.id !== undefined ? item.id : item.Id };
                        })
                    };
                },
                cache: true
            }
        });
    }

    $(function () {
        movimientoSelect('#Area', "{{ url('/Ajax/Area') }}", 'Área', function (item) { return item.Nombre; });
        movimientoSelect('#Empleado', "{{ url('/Ajax/Empleado') }}", 'Empleado', function (item) {
            return item.cedula + ' | ' + item.nombre + ' ' + item.apellidos;
        });
    });
</script>
@endpush

@section('content')
@php($item = $Inventario->first())

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">Registrar movimiento</h3>
            </div>

            @if (! $item)
                <div class="card-body">
                    <p class="inv-texto-2 mb-0">No se encontró el producto en el inventario.</p>
                </div>
                <div class="card-footer inv-acciones inv-acciones--fin">
                    <a class="btn btn-outline-secondary" href="{{ route('Inventario') }}">Volver</a>
                </div>
            @else
                <div class="card-body border-bottom">
                    <div class="inv-texto font-weight-bold">{{ $item->Producto }}</div>
                    <div class="inv-texto-2">Cantidad actual: <strong class="inv-texto">{{ $item->Cantidades }}</strong></div>
                </div>

                <form method="POST" action="{{ route('Guardar_Movimientos') }}">
                    @csrf
                    <input type="hidden" name="Id" value="{{ $item->Id }}">
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0 pl-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="Tipo">Tipo de movimiento <span class="inv-campo-obligatorio">*</span></label>
                            <select id="Tipo" name="Tipo" class="custom-select" required>
                                <option value="" disabled {{ old('Tipo') ? '' : 'selected' }}>Seleccione un tipo</option>
                                <option value="1" {{ old('Tipo') == '1' ? 'selected' : '' }}>Salida</option>
                                <option value="2" {{ old('Tipo') == '2' ? 'selected' : '' }}>Ingreso</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="Cantidad">Cantidad <span class="inv-campo-obligatorio">*</span></label>
                            <input type="number" id="Cantidad" name="Cantidad" min="0" step="any" required
                                   class="form-control" value="{{ old('Cantidad') }}" autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="Area">Área solicitante</label>
                            <select id="Area" name="Area" class="form-control"></select>
                        </div>

                        <div class="form-group">
                            <label for="Empleado">Empleado</label>
                            <select id="Empleado" name="Empleado" class="form-control"></select>
                        </div>

                        <div class="form-group mb-0">
                            <label for="Descripcion">Descripción</label>
                            <textarea id="Descripcion" name="Descripcion" rows="3" class="form-control"
                                      placeholder="Marca, cantidades, descripción de lo que sale o ingresa">{{ old('Descripcion') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer inv-acciones inv-acciones--fin">
                        <a class="btn btn-outline-secondary" href="{{ route('Inventario') }}">Cancelar</a>
                        <button type="submit" class="btn btn-success">Registrar</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
