@extends("theme.$theme.layout")

@section('content')

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

    <!-- Modal -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Cambiar Empresa</h5>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('ActualizarEmpresa') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group">
                                <label>{{ __('Empresa') }}</label>
                                <select class="custom-select" name="Empresa" id="Empresa">
                                    <option selected value="">------------------------------</option>
                                    @foreach ($division as $item)
                                        <option value="{{ $item->id }}"> {{ $item->nombre }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <table class="table">
                            <tr ALIGN="center">
                                <td>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success">{{ __('Cambiar') }}</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group ">
                                        @php
                                            $modulo = session('modulo_seleccionado');
                                            switch ($modulo) {
                                                case 1:
                                                    $ruta = route('InicioInventario');
                                                    break;
                                                case 2:
                                                    $ruta = route('InicioInventario');
                                                    break;
                                                case 3:
                                                    $ruta = route('BonoRegalo.inicio');
                                                    break;
                                                default:
                                                    $ruta = route('InicioInventario');
                                                    break;
                                            }
                                        @endphp
                                        <a class="btn btn-secondary" href="{{ $ruta }}">
                                            <i class="fas fa-times me-1"></i>Cancelar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#myModal').modal({
                backdrop: 'static',
                keyboard: false
            }).modal('show')
        });
    </script>
@endsection
