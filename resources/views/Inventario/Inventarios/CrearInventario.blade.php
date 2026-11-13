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
    // Selectores con busqueda remota. Las URL se generan con url() para respetar el prefijo /Inventario/public.
    function inventarioSelect(selector, endpoint, etiqueta) {
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
                            return { text: item.Nombre, id: item.Id };
                        })
                    };
                },
                cache: true
            }
        });
    }

    $(function () {
        inventarioSelect('#Division', "{{ url('/Ajax/Division') }}", 'División');
        inventarioSelect('#Almacen', "{{ url('/Ajax/Almacen') }}", 'Almacén');
        inventarioSelect('#Categoria', "{{ url('/Ajax/Categoria') }}", 'Categoría');
        inventarioSelect('#Producto', "{{ url('/Ajax/Producto') }}", 'Producto');
    });
</script>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">Agregar producto al inventario</h3>
            </div>

            <form method="POST" action="{{ route('Guardar_Inventario') }}">
                @csrf
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

                    <p class="inv-ayuda mb-3">Todos los campos son obligatorios. Escriba en los buscadores para elegir un valor de la lista.</p>

                    <h5 class="inv-texto font-weight-bold mb-2">Ubicación</h5>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="Division">División <span class="inv-campo-obligatorio">*</span></label>
                            <select id="Division" name="Division" class="form-control"></select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="Almacen">Almacén <span class="inv-campo-obligatorio">*</span></label>
                            <select id="Almacen" name="Almacen" class="form-control"></select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="Categoria">Categoría <span class="inv-campo-obligatorio">*</span></label>
                            <select id="Categoria" name="Categoria" class="form-control"></select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="Producto">Producto <span class="inv-campo-obligatorio">*</span></label>
                            <select id="Producto" name="Producto" class="form-control"></select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="Estanteria">Estantería <span class="inv-campo-obligatorio">*</span></label>
                            <input type="text" id="Estanteria" name="Estanteria" class="form-control"
                                   value="{{ old('Estanteria') }}" autocomplete="off" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="Entrepaño">Entrepaño <span class="inv-campo-obligatorio">*</span></label>
                            <input type="text" id="Entrepaño" name="Entrepaño" class="form-control"
                                   value="{{ old('Entrepaño') }}" autocomplete="off" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="Gaveta">Gaveta <span class="inv-campo-obligatorio">*</span></label>
                            <input type="text" id="Gaveta" name="Gaveta" class="form-control"
                                   value="{{ old('Gaveta') }}" autocomplete="off" required>
                        </div>
                    </div>

                    <h5 class="inv-texto font-weight-bold mb-2 mt-2">Cantidades</h5>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="Cantidades">Cantidad en inventario <span class="inv-campo-obligatorio">*</span></label>
                            <input type="number" id="Cantidades" name="Cantidades" class="form-control"
                                   value="{{ old('Cantidades') }}" min="0" step="any" autocomplete="off" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="CantidadMin">Cantidad mínima <span class="inv-campo-obligatorio">*</span></label>
                            <input type="number" id="CantidadMin" name="CantidadMin" class="form-control"
                                   value="{{ old('CantidadMin') }}" min="0" step="any" autocomplete="off" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="CantidadMax">Cantidad máxima <span class="inv-campo-obligatorio">*</span></label>
                            <input type="number" id="CantidadMax" name="CantidadMax" class="form-control"
                                   value="{{ old('CantidadMax') }}" min="0" step="any" autocomplete="off" required>
                        </div>
                    </div>
                </div>

                <div class="card-footer inv-acciones inv-acciones--fin">
                    <a href="{{ route('Inventario') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
