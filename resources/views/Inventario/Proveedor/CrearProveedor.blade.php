@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">Crear proveedor</h3>
            </div>

            <form method="POST" action="{{ route('Guardar_Proveedor') }}">
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
                        <label for="Nit">NIT <span class="inv-campo-obligatorio">*</span></label>
                        <input type="text" id="Nit" name="Nit" inputmode="numeric" maxlength="15"
                               class="form-control @error('Nit') is-invalid @enderror"
                               value="{{ old('Nit') }}" autocomplete="off" autofocus required>
                        <small class="form-text inv-ayuda">Solo números, sin dígito de verificación.</small>
                        @error('Nit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="Nombre">Razón social <span class="inv-campo-obligatorio">*</span></label>
                        <input type="text" id="Nombre" name="Nombre" maxlength="255"
                               class="form-control @error('Nombre') is-invalid @enderror"
                               value="{{ old('Nombre') }}" autocomplete="off" required>
                        @error('Nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="Telefono">Teléfono</label>
                        <input type="text" id="Telefono" name="Telefono" maxlength="30"
                               class="form-control" value="{{ old('Telefono') }}" autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="Correo">Correo electrónico</label>
                        <input type="email" id="Correo" name="Correo" maxlength="150"
                               class="form-control @error('Correo') is-invalid @enderror"
                               value="{{ old('Correo') }}" autocomplete="off">
                        @error('Correo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer inv-acciones inv-acciones--fin">
                    <a href="{{ route('Proveedor') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
