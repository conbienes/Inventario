@extends("theme.$theme.layout")

@section('content')

    <div class="card card-primary  ">
        <div class="card-header ">
            <h3 class="card-title">{{ __('Proveedor') }}</h3>
        </div>
        <form action="{{ route('Proveedor') }}" method="GET">
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
                </div>
        </form>
        <td>
            <div class="form-group ">
                <a class="btn btn-success" name="Crear" id="Crear"
                    href="{{ route('Crear_Proveedor') }}">{{ __('Crear') }} </a>
            </div>
        </td>
        </tr>
        </table>

        <div class="card-header ">
            <div class="row justify-content-center">

                <table class="table" style="font-size: 12px;">
                    @if (count($Proveedor) <= 0)
                        <tr>
                            <td>NO HAY RESULTADO</td>
                        </tr>
                    @else
                        <thead>
                            <tr>
                                <th scope="col">Nit</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Telefono</th>
                                <th scope="col">Correo</th>
                                <th scope="col">Editar</th>
                            </tr>
                        </thead>

                        @foreach ($Proveedor as $item)
                            <tr>
                                <td> {{ $item->Nit }} </td>
                                <td>{{ $item->Nombre }} </td>
                                <td>{{ $item->Telefono }} </td>
                                <td>{{ $item->Correo }} </td>
                                <td> <a href="{{ route('editar_Proveedor', ['id' => $item->Id]) }}"> <i
                                            class="fas fa-edit nav-icon"></i>Editar</a> </td>
                            </tr>
                        @endforeach
                    @endif
                </table>
                <div style="text-align:center;">
                    <table ALIGN="center">
                        <tr>
                            <td ALIGN="center">
                                <p> {{ $Proveedor->total() }} Registros | Página {{ $Proveedor->currentPage() }} de
                                    {{ $Proveedor->lastPage() }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{ $Proveedor->links() }}
                            </td>
                        <tr>
                        <tr ALIGN="center">
                        </tr>
                    </table>
                </div>
            </div>
        @endsection
