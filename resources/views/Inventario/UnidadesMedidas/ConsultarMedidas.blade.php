@extends("theme.$theme.layout")

@section('content')

    <div class="card card-primary  ">
        <div class="card-header ">
            <h3 class="card-title">{{ __('Unidades de Medidas') }}</h3>
        </div>
        @if (session('succes'))
            <div class="alert alert-success alert-dismissible" data-auto-dismiss="3000">

                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <ul>
                    <li>{{ session('succes') }}</li>
                </ul>
            </div>

    </div>
    @endif
    <form action="{{ route('UnidMedida') }}" method="GET">
        <table class="form-group col-lg-5 " cellpadding="4">
            <div class="form-group">
                <tr>
                    <td>
                    </td>
                    <td>
                        <div class="form-group ">
                            <input type="text" class="form-control" name="texto" id="texto" autocomplete="off">
                        </div>
                    </td>
                    <td>
                        <div class="form-group ">
                            <button type="submit" name="Buscar" id="Buscar"
                                class="btn btn-primary">{{ __('Buscar') }}</button>
                        </div>
                    </td>
                    <td>
                        <div class="form-group ">
                            <a class="btn btn-success" name="Crear" id="Crear"
                                href="{{ route('Crear_Medida') }}">{{ __('Crear') }}
                            </a>
                        </div>
                    </td>
                </tr>
    </form>
    </div>
    </table>


    <div class="card-header ">
        <div class="row justify-content-center">
            <table class="table">
                @if (count($UnidadesM) <= 0)
                    <tr ALING='CENTER'>
                        <td>NO HAY RESULTADO</td>
                    </tr>
                @else
                    <thead>
                        <tr>
                            <th scope="col">Id</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Abreviatura</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Editar</th>
                        </tr>
                    </thead>

                    @foreach ($UnidadesM as $item)
                        <tr>
                            <td>{{ $item->Id }} </td>
                            <td>{{ $item->Nombre }} </td>
                            <td> {{ $item->Abreviatura }} </td>
                            <td>
                                @if ($item->Estado == 1)
                                    Activo
                                @else
                                    Inactivo
                                @endif
                            </td>
                            <td> <a href="{{ route('editar_UnidMedida', ['id' => $item->Id]) }}"> <i
                                        class="fas fa-edit nav-icon"></i>Editar</a> </td>
                        </tr>
                    @endforeach
                @endif
            </table>
            <div class="panel-body text-center">
                <!-- Paginación -->
                <div class="d-flex justify-content-center">
                    {{ $UnidadesM->links() }}
                </div>

                <!-- Información de registros y páginas -->
                <p class="mt-2">
                    {{ $UnidadesM->total() }} Registros | Página {{ $UnidadesM->currentPage() }} de {{ $UnidadesM->lastPage() }}
                </p>
            </div>

        </div>
    </div>
    </div>

@endsection
