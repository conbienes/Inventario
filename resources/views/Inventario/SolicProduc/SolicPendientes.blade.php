@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Mis solicitudes pendientes</h1>
        <p class="inv-subtitulo">Solicitudes de productos enviadas que aún no han sido aprobadas.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('InicioInventario') }}"><i class="fas fa-home"></i> Inicio</a>
        <a class="btn btn-success" href="{{ route('Crear_SolicProduc') }}"><i class="fas fa-plus"></i> Nueva solicitud</a>
    </div>
</div>

@include('Inventario.partials.filtros', [
    'accion' => route('Pendientes'),
    'total'  => $pedidoinventario->total(),
    'campos' => [
        ['tipo' => 'fecha',  'nombre' => 'desde', 'etiqueta' => 'Desde'],
        ['tipo' => 'fecha',  'nombre' => 'hasta', 'etiqueta' => 'Hasta'],
        ['tipo' => 'orden',  'nombre' => 'orden', 'defecto' => 'recientes', 'opciones' => ['recientes' => 'Más recientes', 'antiguas' => 'Más antiguas']],
    ],
])

<div class="inv-tabla-wrap">
    <div class="table-responsive">
        <table class="table inv-tabla">
            <thead>
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Fecha solicitada</th>
                    <th scope="col" class="num"># Productos</th>
                    <th scope="col">Estado</th>
                    <th scope="col" class="text-center">Ver</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidoinventario as $item)
                    @if ((int) $item->Estado === 1)
                        <tr>
                            <td>{{ $item->Id }}</td>
                            <td>{{ $item->Dia }}</td>
                            <td class="num">{{ $item->Cantidad }}</td>
                            <td>@include('Inventario.SolicProduc._estado', ['estado' => $item->Estado])</td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('VistaPendientes', ['id' => Crypt::encrypt($item->Id)]) }}"><i class="far fa-eye"></i> Ver detalle</a>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="inv-vacio"><i class="fas fa-inbox"></i><p>No tiene solicitudes pendientes.</p></div>
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
