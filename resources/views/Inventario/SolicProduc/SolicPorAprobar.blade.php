@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Solicitudes por aprobar</h1>
        <p class="inv-subtitulo">Revise las solicitudes de productos enviadas por el personal.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('InicioInventario') }}"><i class="fas fa-home"></i> Inicio</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
@endif
@if (session('warning'))
    <div class="alert inv-alerta-ambar" role="alert">{{ session('warning') }}</div>
@endif
@if (session('success1'))
    <div class="alert alert-danger" role="alert">{{ session('success1') }}</div>
@endif

@include('Inventario.partials.filtros', [
    'accion' => route('Solicitudes'),
    'total'  => $pedidoinventario->total(),
    'campos' => [
        ['tipo' => 'texto',  'nombre' => 'texto', 'placeholder' => 'Empleado o número de solicitud'],
        ['tipo' => 'select', 'nombre' => 'area', 'etiqueta' => 'Área', 'todos' => 'Todas', 'opciones' => $areas],
        ['tipo' => 'fecha',  'nombre' => 'desde', 'etiqueta' => 'Desde'],
        ['tipo' => 'fecha',  'nombre' => 'hasta', 'etiqueta' => 'Hasta'],
        ['tipo' => 'orden',  'nombre' => 'orden', 'defecto' => 'recientes', 'opciones' => ['recientes' => 'Más recientes', 'antiguas' => 'Más antiguas', 'tentativa' => 'Fecha tentativa de entrega']],
    ],
])

<div class="inv-tabla-wrap">
    <div class="table-responsive">
        <table class="table inv-tabla">
            <thead>
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Solicitante</th>
                    <th scope="col" class="num"># Productos</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Fecha solicitud</th>
                    <th scope="col">Fecha tentativa</th>
                    <th scope="col" class="text-center">Revisar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidoinventario as $item)
                    @if ((int) $item->Estado === 1)
                        <tr>
                            <td>{{ $item->Id }}</td>
                            <td>{{ $item->NombreS }} {{ $item->ApellidosS }}</td>
                            <td class="num">{{ $item->Cantidad }}</td>
                            <td>@include('Inventario.SolicProduc._estado', ['estado' => $item->Estado])</td>
                            <td>{{ $item->Dia }}</td>
                            <td>{{ $item->fecha }}</td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('Cumplir', ['id' => Crypt::encrypt($item->Id)]) }}"><i class="far fa-eye"></i> Revisar</a>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="inv-vacio"><i class="fas fa-inbox"></i><p>No hay solicitudes pendientes.</p></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="inv-pie mt-3">
    <span class="inv-texto-2">{{ $pedidoinventario->total() }} registros | Página {{ $pedidoinventario->currentPage() }} de {{ $pedidoinventario->lastPage() }}</span>
    <div class="inv-paginacion">{{ $pedidoinventario->links('pagination::bootstrap-4') }}</div>
</div>
@endsection
