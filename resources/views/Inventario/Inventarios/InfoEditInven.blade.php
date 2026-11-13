@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Movimientos del producto</h1>
        <p class="inv-subtitulo">Historial de entradas y salidas registradas para este producto.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('Inventario') }}">{{ __('Volver') }}</a>
    </div>
</div>

<div class="inv-tabla-wrap">
    <div class="table-responsive">
        <table class="table inv-tabla" data-filtro-rapido>
            <thead>
                <tr>
                    <th scope="col">Producto</th>
                    <th scope="col" class="text-right num">Cantidad</th>
                    <th scope="col">Tipo de movimiento</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Empleado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($Inventario as $item)
                    <tr>
                        <td>{{ $item->Producto }}</td>
                        <td class="text-right num">{{ $item->Cantidad }}</td>
                        <td>{{ $item->Nombre }}</td>
                        <td>{{ $item->Descripcion }}</td>
                        <td>{{ date('d-m-Y', strtotime($item->FCreacion)) }}</td>
                        <td>{{ trim($item->Empleado . ' ' . $item->Apellidos) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="inv-vacio"><i class="fas fa-exchange-alt"></i><p>Este producto no tiene movimientos.</p></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
