@extends("theme.$theme.layout")

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header bg-warning">
                    <h3 class="card-title">{{ __('Editar Tarjeta de Bono') }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>

                {{-- Errores --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <h5><i class="icon fas fa-ban"></i> Error en el formulario</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulario --}}
                <form method="POST" action="{{ route('BonoRegalo.actualizarTarjeta', $tarjeta->id) }}">
                    @csrf
                    @method('PUT') {{-- Importante para editar --}}
                    <div class="card-body">
                        <div class="row">
                            <!-- Columna izquierda -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero">Número de Tarjeta <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="numero" name="numero"
                                           value="{{ old('numero', $tarjeta->numero) }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="nit">NIT Asociado <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nit" name="nit"
                                           value="{{ old('nit', $tarjeta->nit) }}" required>
                                </div>
                            </div>

                            <!-- Columna derecha -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="valor">Valor (COP) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="valor" name="valor"
                                           value="{{ old('valor', $tarjeta->valor) }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="estado" name="estado" required>
                                        <option value="">Seleccione...</option>
                                        <option value="activa" {{ old('estado', $tarjeta->estado) == 'activa' ? 'selected' : '' }}>Activa</option>
                                        <option value="inactiva" {{ old('estado', $tarjeta->estado) == 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-success mr-2">
                            <i class="fas fa-save"></i> {{ __('Actualizar') }}
                        </button>
                        <a href="{{ route('BonoRegalo.BuscarTarjeta') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('Volver') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
    });
</script>
@endpush
