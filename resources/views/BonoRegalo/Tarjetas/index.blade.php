@extends("theme.$theme.layout")

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header bg-primary">
                    <h3 class="card-title">{{ __('Registro de Nueva Tarjeta de Bono') }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>

                {{-- Mensaje de éxito --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Éxito</h5>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                {{-- Errores de validación --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Error en el formulario</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulario --}}
                <form method="POST" action="{{ route('BonoRegalo.crearTarjeta') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Columna izquierda -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero">Número de Tarjeta <span class="text-danger">*</span> <i class="fas fa-credit-card"></i></label>
                                    <input type="number" class="form-control" id="numero" name="numero"
                                           value="{{ old('numero') }}" required
                                           placeholder="Ingrese el número de tarjeta">
                                </div>

                                <div class="form-group">
                                    <label for="nit">NIT Asociado <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nit" name="nit"
                                           value="{{ old('nit') }}" required
                                           placeholder="Ingrese el NIT">
                                </div>
                            </div>

                            <!-- Columna derecha -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="valor">Valor (COP) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="valor" name="valor"
                                           value="{{ old('valor') }}" required
                                           placeholder="Ingrese el valor de la tarjeta">
                                </div>

                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="estado" name="estado" required>
                                        <option value="">Seleccione...</option>
                                        <option value="activa" {{ old('estado') == 'activa' ? 'selected' : '' }}>Activa</option>
                                        <option value="inactiva" {{ old('estado') == 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-save"></i> {{ __('Guardar Tarjeta') }}
                        </button>
                        <a href="{{ route('BonoRegalo.BuscarTarjeta') }}" class="btn btn-danger">
                            <i class="fas fa-times"></i> {{ __('Cancelar') }}
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
