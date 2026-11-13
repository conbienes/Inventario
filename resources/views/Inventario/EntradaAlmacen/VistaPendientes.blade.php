@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Pedidos pendientes de procesar') }}</h1>
        <p class="inv-subtitulo">Revise los productos recibidos y apruebe las entregas.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('Vista_EntAlmacen', ['id' => Crypt::encrypt($div)]) }}"><i class="fas fa-arrow-left"></i> {{ __('Volver') }}</a>
    </div>
</div>

@php($urlLista = route('SolicPendFacturas', ['id' => Crypt::encrypt($div)]))
@include('Inventario.partials.filtros', [
    'accion' => $urlLista,
    'total' => $SoliPendi->total(),
    'campos' => [
        ['tipo' => 'texto', 'nombre' => 'texto', 'placeholder' => 'Proveedor, NIT, orden de pedido o remisión'],
        ['tipo' => 'fecha', 'nombre' => 'desde', 'etiqueta' => 'Ingreso desde'],
        ['tipo' => 'fecha', 'nombre' => 'hasta', 'etiqueta' => 'Ingreso hasta'],
        ['tipo' => 'numero', 'nombre' => 'valor_min', 'etiqueta' => 'Valor mínimo', 'prefijo' => '$', 'placeholder' => '$'],
        ['tipo' => 'numero', 'nombre' => 'valor_max', 'etiqueta' => 'Valor máximo', 'prefijo' => '$', 'placeholder' => '$'],
        ['tipo' => 'orden', 'nombre' => 'orden', 'defecto' => 'recientes', 'opciones' => ['recientes' => 'Más recientes', 'antiguas' => 'Más antiguas', 'valor_desc' => 'Mayor valor', 'valor_asc' => 'Menor valor', 'proveedor' => 'Proveedor (A-Z)']],
    ],
])

<div class="inv-tabla-wrap">
    <div class="table-responsive">
        <table class="table inv-tabla">
            <thead>
                <tr>
                    <th scope="col">Proveedor</th>
                    <th scope="col">Remisión / Orden</th>
                    <th scope="col">Ingreso</th>
                    <th scope="col" class="text-right">Valor</th>
                    <th scope="col">Estado</th>
                    <th scope="col"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($SoliPendi as $item)
                    <tr>
                        <td>
                            <div class="font-weight-bold">{{ $item->Proveedor }}</div>
                            <div class="small text-muted">NIT {{ $item->Nit }}</div>
                        </td>
                        <td>
                            <div>{{ $item->Nfactura }}</div>
                            <div class="small text-muted">OP {{ $item->OrdenPedido }}</div>
                        </td>
                        <td class="text-nowrap">{{ $item->FechaIngreso ? \Carbon\Carbon::parse($item->FechaIngreso)->locale('es')->isoFormat('D MMM YYYY') : '—' }}</td>
                        <td class="text-right text-nowrap">${{ number_format((float) $item->PrecioTotal, 0, ',', '.') }}</td>
                        <td><span class="inv-estado inv-estado--alerta">{{ $item->Estado }}</span></td>
                        <td class="text-nowrap text-right">
                            <a class="btn btn-sm btn-primary" title="Revisar y aprobar productos recibidos"
                               href="{{ route('AprobarFactura', ['id' => Crypt::encrypt($item->Id), 'div' => $div]) }}">
                                Revisar <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="inv-vacio"><i class="fas fa-inbox"></i><p>{{ request()->hasAny(['texto', 'desde', 'hasta', 'valor_min', 'valor_max']) ? 'No hay pedidos que coincidan con los filtros.' : 'No hay pedidos pendientes de procesar para esta división.' }}</p></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($SoliPendi->lastPage() > 1)
    <div class="inv-pie mt-3">
        <span class="inv-texto-2">Página {{ $SoliPendi->currentPage() }} de {{ $SoliPendi->lastPage() }}</span>
        <div class="inv-paginacion">{{ $SoliPendi->links('pagination::bootstrap-4') }}</div>
    </div>
@endif
@endsection
