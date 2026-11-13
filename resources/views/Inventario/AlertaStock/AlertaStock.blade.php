@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
{{-- Formulario de exportación: el botón Exportar lo referencia con form= --}}
<form id="formExportarAlertas" method="POST" action="{{ route('Exportar_Alertas') }}" class="d-none">
    @csrf
    <input type="hidden" name="IdEmpresa" value="{{ $Division->id }}">
</form>

@if (session('succes'))
    <div class="alert alert-success" role="alert"><i class="fas fa-check-circle mr-1"></i> {{ session('succes') }}</div>
@endif

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Alertas de stock: {{ $Division->nombre }}</h1>
        <p class="inv-subtitulo">Productos por debajo de su cantidad mínima. Ajusta el stock aquí mismo cuando repongas.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('InicioInventario') }}">{{ __('Volver') }}</a>
        <button type="submit" form="formExportarAlertas" class="btn btn-outline-primary">
            <i class="fas fa-file-excel mr-1"></i> Exportar
        </button>
    </div>
</div>

@include('Inventario.partials.selector-division', ['ruta' => 'AlertaStock'])

@include('Inventario.partials.filtros', [
    'accion' => route('AlertaStock', ['id' => Crypt::encrypt($Division->id)]),
    'total' => $Inventario->total(),
    'campos' => [
        ['tipo' => 'texto', 'nombre' => 'texto', 'placeholder' => 'Nombre o código del producto'],
        ['tipo' => 'select', 'nombre' => 'estado', 'etiqueta' => 'Gravedad', 'opciones' => ['sin' => 'Sin existencia', 'bajo' => 'Bajo el mínimo (con algo)']],
        ['tipo' => 'select', 'nombre' => 'categoria', 'etiqueta' => 'Categoría', 'todos' => 'Todas', 'opciones' => $opciones['categorias']->pluck('Nombre', 'Id')->all()],
        ['tipo' => 'select', 'nombre' => 'almacen', 'etiqueta' => 'Almacén', 'opciones' => $opciones['almacenes']->pluck('Nombre', 'Id')->all()],
        ['tipo' => 'orden', 'nombre' => 'orden', 'defecto' => 'faltante', 'opciones' => \App\Services\Inventario\FiltrosInventario::ORDENES],
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
                    <th scope="col" style="min-width:170px">Stock vs. mínimo</th>
                    <th scope="col" class="text-right">Faltan</th>
                    <th scope="col" class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($Inventario as $item)
                    @php
                        $cant = (float) $item->Cantidades;
                        $min = (float) $item->CantidadMin;
                        $max = (float) $item->CantidadMax;
                        $pct = $min > 0 ? max(0, min(100, round($cant / $min * 100))) : 0;
                        $faltan = max(0, ($max > $min ? $max : $min) - $cant);
                        $datos = 'data-id="' . $item->Id . '" data-producto="' . e($item->Producto) . '" data-codigo="' . e($item->Codigo)
                            . '" data-actual="' . ($cant + 0) . '" data-min="' . ($min + 0) . '" data-max="' . ($max + 0) . '"';
                    @endphp
                    <tr>
                        <td>
                            <div class="font-weight-bold">{{ $item->Producto }}</div>
                            <small class="inv-texto-2">Código {{ $item->Codigo }} · {{ $item->Almacen }}</small>
                        </td>
                        <td>{{ $item->Categoria }}</td>
                        <td>
                            <div class="d-flex justify-content-between small mb-1">
                                <span>
                                    @if ($cant <= 0)
                                        <span class="inv-estado inv-estado--critico">Sin existencia</span>
                                    @else
                                        <strong>{{ $cant + 0 }}</strong> de {{ $min + 0 }}
                                    @endif
                                </span>
                                <span class="text-muted">Máx {{ $max + 0 }}</span>
                            </div>
                            <div class="inv-avance"><span style="width: {{ $pct }}%; background: {{ $cant <= 0 ? '#B91C1C' : '#F59E0B' }}"></span></div>
                        </td>
                        <td class="text-right"><strong>{{ $faltan + 0 }}</strong><small class="d-block inv-texto-2">hasta el {{ $max > $min ? 'máximo' : 'mínimo' }}</small></td>
                        <td class="text-right text-nowrap">
                            <button type="button" class="btn btn-sm btn-primary js-ajustar" {!! $datos !!}>
                                <i class="fas fa-plus mr-1"></i> Reponer
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary js-limites" {!! $datos !!} data-toggle="tooltip" title="Mínimo y máximo" aria-label="Mínimo y máximo">
                                <i class="fas fa-arrows-alt-v"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="inv-vacio">
                                <i class="fas fa-check-circle" style="color:#15803D"></i>
                                <p>{{ request()->hasAny(['texto', 'estado', 'categoria', 'almacen']) ? 'No hay alertas que coincidan con los filtros.' : 'Ningún producto está por debajo de su mínimo.' }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($Inventario->lastPage() > 1)
    <div class="inv-pie mt-3">
        <span class="inv-texto-2">Página {{ $Inventario->currentPage() }} de {{ $Inventario->lastPage() }}</span>
        <div class="inv-paginacion">{{ $Inventario->links('pagination::bootstrap-4') }}</div>
    </div>
@endif
@endsection
