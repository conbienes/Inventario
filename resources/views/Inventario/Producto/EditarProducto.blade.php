@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">Editar producto</h3>
            </div>

            <form method="POST" action="{{ route('actualizar_Producto') }}">
                @csrf
                <input type="hidden" name="Id" value="{{ $Producto->Id }}">
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
                        <label for="Codigo">Código</label>
                        <input type="text" id="Codigo" name="Codigo" maxlength="50"
                               class="form-control @error('Codigo') is-invalid @enderror"
                               value="{{ old('Codigo', $Producto->Codigo) }}" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="Nombre">Nombre del producto <span class="inv-campo-obligatorio">*</span></label>
                        <input type="text" id="Nombre" name="Nombre" maxlength="100"
                               class="form-control @error('Nombre') is-invalid @enderror"
                               value="{{ old('Nombre', $Producto->Nombre) }}" autocomplete="off" required>
                        @error('Nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="UnidadMedida">Unidad de medida <span class="inv-campo-obligatorio">*</span></label>
                        <select id="UnidadMedida" name="UnidadMedida" class="custom-select" required>
                            <option value="">Seleccione una unidad</option>
                            @foreach ($UnidadMedida as $item)
                                <option value="{{ $item->Id }}" {{ old('UnidadMedida', $Producto->IdUnidadMedida) == $item->Id ? 'selected' : '' }}>{{ $item->Nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="Estado">Estado <span class="inv-campo-obligatorio">*</span></label>
                        <select id="Estado" name="Estado" class="custom-select" required>
                            <option value="1" {{ old('Estado', $Producto->Estado) == 1 ? 'selected' : '' }}>Activo</option>
                            <option value="2" {{ old('Estado', $Producto->Estado) == 2 ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="card-footer inv-acciones inv-acciones--fin">
                    <a href="{{ route('Producto') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
