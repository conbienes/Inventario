@extends("theme.$theme.layout")

@section('content')
    @include('regEmpleado.partials.estilos')
    @php
        $rutaVolver = session('modulo_seleccionado') == 3 ? route('BonoRegalo.inicio') : route('InicioInventario');
    @endphp

    <div class="emp-page container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title emp-titulo">
                            <i class="fas fa-user-plus mr-2"></i>Registrar empleado
                        </h3>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger mx-3 mt-3 mb-0" role="alert">
                            <h6 class="font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i>No se pudo registrar al empleado</h6>
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('crear_Empleado') }}">
                        @csrf
                        <div class="card-body">
                            @include('regEmpleado.partials.campos', ['empleado' => null, 'esEdicion' => false])

                            @include('regEmpleado.partials.permisos', [
                                'modulos' => $modulos,
                                'permisos' => $permisos,
                                'permisosSeleccionados' => old('modulos', []),
                                'especialesSeleccionados' => old('permisos_especiales', []),
                                'catalogoEspeciales' => $catalogoEspeciales,
                            ])
                        </div>

                        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center">
                            <a class="btn btn-outline-secondary mb-2" href="{{ $rutaVolver }}">
                                <i class="fas fa-arrow-left mr-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-emp-primario mb-2">
                                <i class="fas fa-save mr-1"></i>Crear empleado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
