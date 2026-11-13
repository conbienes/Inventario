@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Mis solicitudes aprobadas</h1>
        <p class="inv-subtitulo">Solicitudes de productos que el almacén ya aprobó.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('InicioInventario') }}"><i class="fas fa-home"></i> Inicio</a>
    </div>
</div>

@include('Inventario.partials.filtros', [
    'accion' => route('Aprobadas'),
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
                    <th scope="col">Fecha solicitud</th>
                    <th scope="col">Solicitante</th>
                    <th scope="col" class="num"># Productos</th>
                    <th scope="col">Estado</th>
                    <th scope="col" class="text-center">Ver</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidoinventario as $item)
                    <tr>
                        <td>{{ $item->Id }}</td>
                        <td>{{ $item->Dia }}</td>
                        <td>{{ $item->Empleado }} {{ $item->Apellido }}</td>
                        <td class="num">{{ $item->Cantidad }}</td>
                        <td>@include('Inventario.SolicProduc._estado', ['estado' => $item->Estado])</td>
                        <td class="text-center">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('VistaAprobados', ['id' => Crypt::encrypt($item->Id)]) }}"><i class="far fa-eye"></i> Ver detalle</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="inv-vacio"><i class="fas fa-inbox"></i><p>No tiene solicitudes aprobadas.</p></div>
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
