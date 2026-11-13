@extends("theme.$theme.layout")

@section('content')
    @php
        $rutaCancelar = session('modulo_seleccionado') == 3 ? route('BonoRegalo.inicio') : route('InicioInventario');
        $empresaActual = (string) old('Empresa', auth()->user()->id_div ?? '');
    @endphp

    {{-- Se muestra al entrar. No se cierra con clic fuera ni con Esc: solo con Cancelar o Cambiar. --}}
    <div class="modal fade" id="modalCambiarEmpresa" tabindex="-1" role="dialog"
        aria-labelledby="modalCambiarEmpresaTitulo" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="modalCambiarEmpresaTitulo">
                        <i class="fas fa-exchange-alt mr-2"></i>Cambiar empresa
                    </h5>
                </div>

                <form method="POST" action="{{ route('ActualizarEmpresa') }}">
                    @csrf
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0 pl-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group mb-0">
                            <label for="Empresa" class="font-weight-bold">Empresa</label>
                            <select class="custom-select" name="Empresa" id="Empresa" required>
                                <option value="" disabled {{ $empresaActual === '' ? 'selected' : '' }}>Seleccione una empresa</option>
                                @foreach ($division as $item)
                                    <option value="{{ $item->id }}" {{ $empresaActual === (string) $item->id ? 'selected' : '' }}>
                                        {{ $item->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a class="btn btn-outline-secondary" href="{{ $rutaCancelar }}">
                            <i class="fas fa-times mr-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check mr-1"></i>Cambiar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function () {
            $('#modalCambiarEmpresa').modal('show');
        });
    </script>
@endpush
