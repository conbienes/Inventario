@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Solicitudes aprobadas</h1>
        <p class="inv-subtitulo">
            @if (session('FechaInicial') || session('FechaFinal') || session('Empleados'))
                Filtro activo:
                @if (session('FechaInicial') && session('FechaFinal'))
                    del {{ session('FechaInicial') }} al {{ session('FechaFinal') }}
                @endif
                @if (session('Empleados'))
                    | empleado código {{ session('Empleados') }}
                @endif
            @else
                Historial de solicitudes aprobadas por el almacén.
            @endif
        </p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('InicioInventario') }}"><i class="fas fa-home"></i> Inicio</a>
        <a class="btn btn-outline-primary" href="{{ route('TotalAprobadas', ['limpiar' => true]) }}"><i class="fas fa-undo"></i> Limpiar filtros</a>
        <a class="btn btn-primary" href="{{ route('Filtro') }}"><i class="fas fa-filter"></i> Filtrar</a>
    </div>
</div>

@include('Inventario.partials.filtros', [
    'accion' => route('TotalAprobadas'),
    'total'  => $pedidoinventario->total(),
    'campos' => [
        ['tipo' => 'texto',  'nombre' => 'texto', 'placeholder' => 'Número de solicitud o empleado'],
        ['tipo' => 'select', 'nombre' => 'empleado', 'etiqueta' => 'Empleado', 'todos' => 'Todos', 'opciones' => $empleados],
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
                            @if ((int) $item->Estado === 2)
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('VistaAprobados', ['id' => Crypt::encrypt($item->Id)]) }}"><i class="far fa-eye"></i> Ver detalle</a>
                            @else
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('VistaPendientes', ['id' => Crypt::encrypt($item->Id)]) }}"><i class="far fa-eye"></i> Ver detalle</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="inv-vacio"><i class="fas fa-inbox"></i><p>No hay solicitudes para mostrar.</p></div>
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
