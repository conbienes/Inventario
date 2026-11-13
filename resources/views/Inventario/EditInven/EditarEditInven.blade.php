@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@php($item = $EditInven->first())

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">Editar cantidad del inventario</h3>
            </div>

            @if (! $item)
                <div class="card-body">
                    <p class="inv-texto-2 mb-0">No se encontró el registro de inventario.</p>
                </div>
                <div class="card-footer inv-acciones inv-acciones--fin">
                    <a class="btn btn-outline-secondary" href="{{ route('Inventario') }}">Volver</a>
                </div>
            @else
                <div class="card-body border-bottom">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 inv-texto-2">Empresa</dt>
                        <dd class="col-sm-8 inv-texto">{{ $item->Division }}</dd>
                        <dt class="col-sm-4 inv-texto-2">Almacén</dt>
                        <dd class="col-sm-8 inv-texto">{{ $item->Almacen }}</dd>
                        <dt class="col-sm-4 inv-texto-2">Categoría</dt>
                        <dd class="col-sm-8 inv-texto">{{ $item->Categoria }}</dd>
                        <dt class="col-sm-4 inv-texto-2">Producto</dt>
                        <dd class="col-sm-8 inv-texto font-weight-bold">{{ $item->Producto }}</dd>
                        <dt class="col-sm-4 inv-texto-2">Cantidad actual</dt>
                        <dd class="col-sm-8 inv-texto font-weight-bold">{{ $item->Cantidades }}</dd>
                    </dl>
                </div>

                <form method="POST" action="{{ route('actualizar_EditInven') }}">
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
                            <label for="Cantidad">Diferencia a registrar <span class="inv-campo-obligatorio">*</span></label>
                            <input type="number" id="Cantidad" name="Cantidad" step="any" required
                                   class="form-control @error('Cantidad') is-invalid @enderror"
                                   value="{{ old('Cantidad') }}" placeholder="Ingrese solo la diferencia (puede ser negativa)" autocomplete="off">
                            <small class="form-text inv-ayuda">Se suma a la cantidad actual. Use un valor negativo para restar.</small>
                            @error('Cantidad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label for="Descripcion">Motivo de la modificación <span class="inv-campo-obligatorio">*</span></label>
                            <textarea id="Descripcion" name="Descripcion" rows="3" maxlength="255" required
                                      class="form-control @error('Descripcion') is-invalid @enderror"
                                      placeholder="Describa el motivo del cambio">{{ old('Descripcion') }}</textarea>
                            @error('Descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer inv-acciones inv-acciones--fin">
                        <a class="btn btn-outline-secondary" href="{{ route('Inventario') }}">Cancelar</a>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
