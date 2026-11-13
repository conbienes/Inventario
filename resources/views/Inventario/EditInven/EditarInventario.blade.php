@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@php($item = $EditInven->first())

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">Editar ubicación topográfica</h3>
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
                        <dt class="col-sm-4 inv-texto-2">Categoría</dt>
                        <dd class="col-sm-8 inv-texto">{{ $item->Categoria }}</dd>
                        <dt class="col-sm-4 inv-texto-2">Producto</dt>
                        <dd class="col-sm-8 inv-texto font-weight-bold">{{ $item->Producto }}</dd>
                    </dl>
                </div>

                <form method="POST" action="{{ route('ActualizarInventario') }}">
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
                            <label for="Estanteria">Estantería <span class="inv-campo-obligatorio">*</span></label>
                            <input type="text" id="Estanteria" name="Estanteria" maxlength="50" required
                                   class="form-control @error('Estanteria') is-invalid @enderror"
                                   value="{{ old('Estanteria', $item->Estanteria) }}" autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="Entrepaño">Entrepaño <span class="inv-campo-obligatorio">*</span></label>
                            <input type="text" id="Entrepaño" name="Entrepaño" maxlength="50" required
                                   class="form-control @error('Entrepaño') is-invalid @enderror"
                                   value="{{ old('Entrepaño', $item->Entrepano) }}" autocomplete="off">
                        </div>

                        <div class="form-group mb-0">
                            <label for="Gaveta">Gaveta <span class="inv-campo-obligatorio">*</span></label>
                            <input type="text" id="Gaveta" name="Gaveta" maxlength="50" required
                                   class="form-control @error('Gaveta') is-invalid @enderror"
                                   value="{{ old('Gaveta', $item->Gaveta) }}" autocomplete="off">
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
