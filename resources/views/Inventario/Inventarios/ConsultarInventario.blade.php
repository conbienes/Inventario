@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@foreach (['success', 'succes'] as $clave)
    @if (session($clave))
        <div class="alert alert-success mb-3" role="alert"><i class="fas fa-check-circle mr-1"></i> {{ session($clave) }}</div>
    @endif
@endforeach
@if (session('success1') || session('succes1'))
    <div class="alert inv-alerta-ambar mb-3" role="alert">{{ session('success1') ?? session('succes1') }}</div>
@endif

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Inventario: {{ $Division->nombre }}</h1>
        <p class="inv-subtitulo">Existencias de productos por ubicación y cantidad.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('InicioInventario') }}">{{ __('Volver') }}</a>
        <a href="{{ route('Crear_Inventario') }}" class="btn btn-success"><i class="fas fa-plus"></i> {{ __('Crear') }}</a>
    </div>
</div>

@include('Inventario.partials.selector-division', ['ruta' => 'Consultar_Inventario'])

@include('Inventario.partials.filtros', [
    'accion' => route('Consultar_Inventario', ['id' => Crypt::encrypt($Division->id)]),
    'total' => $Inventarios->total(),
    'campos' => [
        ['tipo' => 'texto', 'nombre' => 'texto', 'placeholder' => 'Nombre o código del producto'],
        ['tipo' => 'select', 'nombre' => 'categoria', 'etiqueta' => 'Categoría', 'todos' => 'Todas', 'opciones' => $opciones['categorias']->pluck('Nombre', 'Id')->all()],
        ['tipo' => 'select', 'nombre' => 'almacen', 'etiqueta' => 'Almacén', 'opciones' => $opciones['almacenes']->pluck('Nombre', 'Id')->all()],
        ['tipo' => 'select', 'nombre' => 'estado', 'etiqueta' => 'Stock', 'opciones' => \App\Services\Inventario\FiltrosInventario::ESTADOS],
        ['tipo' => 'orden', 'nombre' => 'orden', 'defecto' => 'producto', 'opciones' => \App\Services\Inventario\FiltrosInventario::ORDENES],
    ],
])

@include('Inventario.partials.modales-stock')

<div class="inv-tabla-wrap">
    <div class="table-responsive">
        <table class="table inv-tabla">
            <thead>
                <tr>
                    <th scope="col">Producto</th>
                    <th scope="col">Categoría</th>
                    <th scope="col">Ubicación</th>
                    <th scope="col" class="text-right">Cantidad</th>
                    <th scope="col" class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($Inventarios as $item)
                    @php
                        $cant = (float) $item->Cantidades;
                        $estado = $cant <= 0 ? 'critico' : ($cant < (float) $item->CantidadMin ? 'alerta' : 'ok');
                    @endphp
                    <tr>
                        <td>
                            <div class="font-weight-bold">{{ $item->Producto }}</div>
                            <small class="inv-texto-2">Código {{ $item->Codigo }}</small>
                        </td>
                        <td>{{ $item->Categoria }}</td>
                        <td>
                            {{ $item->Almacen }}
                            <small class="d-block inv-texto-2" title="Estantería · Entrepaño · Gaveta">
                                {{ implode(' · ', array_filter([$item->Estanteria, $item->Entrepano, $item->Gaveta], fn ($v) => $v !== null && $v !== '')) ?: 'Sin ubicación' }}
                            </small>
                        </td>
                        <td class="text-right text-nowrap">
                            <span class="inv-estado inv-estado--{{ $estado }}" title="{{ $estado === 'ok' ? 'Stock suficiente' : ($estado === 'alerta' ? 'Bajo el mínimo' : 'Sin existencia') }}">{{ $item->Cantidades + 0 }}</span>
                            <small class="d-block inv-texto-2">Mín {{ $item->CantidadMin + 0 }} · Máx {{ $item->CantidadMax + 0 }}</small>
                        </td>
                        <td class="text-right text-nowrap">
                            @php
                                $datos = 'data-id="' . $item->Id . '" data-producto="' . e($item->Producto) . '" data-codigo="' . e($item->Codigo)
                                    . '" data-actual="' . ($item->Cantidades + 0) . '" data-min="' . ($item->CantidadMin + 0) . '" data-max="' . ($item->CantidadMax + 0) . '"';
                            @endphp
                            <button type="button" class="btn btn-sm btn-primary js-ajustar" {!! $datos !!} title="Ajustar la cantidad">
                                <i class="fas fa-sliders-h mr-1"></i> Ajustar
                            </button>
                            <div class="btn-group btn-group-sm ml-1" role="group" aria-label="Más acciones">
                                <button type="button" class="btn btn-outline-primary js-limites" {!! $datos !!} data-toggle="tooltip" title="Mínimo y máximo" aria-label="Mínimo y máximo">
                                    <i class="fas fa-arrows-alt-v"></i>
                                </button>
                                <a class="btn btn-outline-primary" data-toggle="tooltip" title="Editar ubicación" aria-label="Editar ubicación"
                                   href="{{ route('EditarInventario', ['Id' => Crypt::encrypt($item->Id)]) }}"><i class="fas fa-map-marker-alt"></i></a>
                                <a class="btn btn-outline-primary" data-toggle="tooltip" title="Registrar movimiento" aria-label="Registrar movimiento"
                                   href="{{ route('editar_Movimientos', ['id' => Crypt::encrypt($item->Id)]) }}"><i class="fas fa-shipping-fast"></i></a>
                                <a class="btn btn-outline-primary" data-toggle="tooltip" title="Ver movimientos del producto" aria-label="Ver movimientos del producto"
                                   href="{{ route('Consultar_Producto', ['id' => Crypt::encrypt($item->Id)]) }}"><i class="fas fa-history"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="inv-vacio"><i class="fas fa-search"></i><p>No hay productos que coincidan con los filtros.</p></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($Inventarios->lastPage() > 1)
    <div class="inv-pie mt-3">
        <span class="inv-texto-2">Página {{ $Inventarios->currentPage() }} de {{ $Inventarios->lastPage() }}</span>
        <div class="inv-paginacion">{{ $Inventarios->links('pagination::bootstrap-4') }}</div>
    </div>
@endif
@endsection
