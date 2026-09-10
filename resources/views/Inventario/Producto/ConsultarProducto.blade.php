@extends("theme.$theme.layout")

@section('content')

    <div class="card card-primary">
        <div class="card-header ">
            <h3 class="card-title">{{ __('PRODUCTOS') }}</h3>
        </div>
        <form action="{{ route('Producto') }}" method="GET">
            <table CELLPADDING=5>
                <div class="form-group ">
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
                                    class="btn btn-primary">{{ __('Buscar Productos') }}</button>
                            </div>
                        </td>
                </div>
        </form>
        <td>
            <div class="form-group ">
                <a class="btn btn-success" name="Crear" id="Crear"
                    href="{{ route('Crear_Producto') }}">{{ __('Crear') }} </a>
            </div>
        </td>
        </tr>
        </table>

        <div class="card-header">
            <div class="row justify-content-center">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center">
                        @if (count($Producto) <= 0)
                            <tr>
                                <td colspan="5">NO HAY RESULTADO</td>
                            </tr>
                        @else
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Producto</th>
                                    <th scope="col">Unidad de Medida</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($Producto as $item)
                                    <tr>
                                        <td>{{ $item->Codigo }}</td>
                                        <td>{{ $item->Nombre }}</td>
                                        <td>{{ $item->Medida }}</td>
                                        <td>
                                            @if ($item->Estado == 1)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('editar_Producto', ['id' => $item->Id]) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @endif
                    </table>
                </div>
                <div class="text-center mt-3">
                    @if ($Producto->total() > 0)
                        <p>
                            {{ $Producto->total() }} Registros | Página {{ $Producto->currentPage() }} de {{ $Producto->lastPage() }}
                        </p>
                        {{ $Producto->links('pagination::bootstrap-4') }}
                    @endif
                </div>
            </div>
        </div>



    </div>



@endsection
