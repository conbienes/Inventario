@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">Crear almacén</h3>
            </div>

            <form method="POST" action="{{ route('Guardar_Almacen') }}">
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

                    <div class="form-group">
                        <label for="Nombre">Nombre del almacén <span class="inv-campo-obligatorio">*</span></label>
                        <input type="text" id="Nombre" name="Nombre" maxlength="100"
                               class="form-control @error('Nombre') is-invalid @enderror"
                               value="{{ old('Nombre') }}" autocomplete="off" autofocus required>
                        @error('Nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="Estado">Estado <span class="inv-campo-obligatorio">*</span></label>
                        <select id="Estado" name="Estado" class="custom-select" required>
                            <option value="1" {{ old('Estado', '1') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="2" {{ old('Estado') == '2' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="card-footer inv-acciones inv-acciones--fin">
                    <a href="{{ route('Almacen') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
