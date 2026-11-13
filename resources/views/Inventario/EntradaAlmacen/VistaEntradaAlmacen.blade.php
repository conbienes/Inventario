@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@php
    $div = $Division->first();
    $dinero = fn ($n) => '$' . number_format((float) $n, 0, ',', '.');
@endphp

@if (session('succes'))
    <div class="alert alert-success" role="alert"><i class="fas fa-check-circle mr-1"></i> {{ session('succes') }}</div>
@endif
@if (session('succes1'))
    <div class="alert inv-alerta-ambar" role="alert"><i class="fas fa-exclamation-triangle mr-1"></i> {{ session('succes1') }}</div>
@endif

<section class="inv-hero">
    <span class="inv-hero__ico" aria-hidden="true"><i class="fas fa-dolly-flatbed"></i></span>
    <div class="inv-hero__txt">
        <span class="inv-hero__etiqueta">Entradas de almacén</span>
        <h1>{{ $div->nombre ?? 'Entradas de almacén' }}</h1>
        <p>Registra los pedidos que llegan del proveedor y apruébalos cuando los recibas.</p>
    </div>
    @if ($div)
        <a href="{{ route('Crear_EntAlmacen', ['id' => Crypt::encrypt($div->id)]) }}" class="btn btn-light btn-lg">
            <i class="fas fa-plus mr-1"></i> Nuevo pedido
        </a>
    @endif
</section>

@include('Inventario.partials.selector-division', ['ruta' => 'Vista_EntAlmacen'])

@if (! $div)
    <div class="inv-tarjeta-wrap">
        <div class="inv-vacio"><i class="fas fa-exclamation-circle"></i><p>No se encontró la división seleccionada.</p></div>
    </div>
@else
    {{-- Accesos --}}
    <div class="row">
        <div class="col-12 col-md-4 mb-3">
            <a class="br-modulo" href="{{ route('Crear_EntAlmacen', ['id' => Crypt::encrypt($div->id)]) }}">
                <span class="br-modulo__ico br-ico-azul"><i class="fas fa-file-invoice"></i></span>
                <span class="br-modulo__txt">
                    <strong>Registrar pedido</strong>
                    <small>Remisión y productos que llegan</small>
                </span>
                <i class="fas fa-chevron-right br-modulo__flecha"></i>
            </a>
        </div>
        <div class="col-12 col-md-4 mb-3">
            <a class="br-modulo" href="{{ route('SolicPendFacturas', ['id' => Crypt::encrypt($div->id)]) }}">
                <span class="br-modulo__ico br-ico-ambar"><i class="fas fa-tasks"></i></span>
                <span class="br-modulo__txt">
                    <strong>Por procesar</strong>
                    <small>Revisar y aprobar lo recibido</small>
                </span>
                <span class="br-modulo__num {{ count($Activas) ? 'is-alerta' : '' }}">{{ count($Activas) }}</span>
                <i class="fas fa-chevron-right br-modulo__flecha"></i>
            </a>
        </div>
        <div class="col-12 col-md-4 mb-3">
            <a class="br-modulo" href="{{ route('SolicAprobadasFacturas', ['id' => Crypt::encrypt($div->id)]) }}">
                <span class="br-modulo__ico br-ico-verde"><i class="fas fa-check-double"></i></span>
                <span class="br-modulo__txt">
                    <strong>Aprobados</strong>
                    <small>Pedidos ya recibidos completos</small>
                </span>
                <span class="br-modulo__num">{{ count($Terminadas) }}</span>
                <i class="fas fa-chevron-right br-modulo__flecha"></i>
            </a>
        </div>
    </div>

    {{-- Pedidos por procesar --}}
    <div class="inv-tarjeta mt-1">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:.5rem">
            <h2 class="mb-0">Pedidos por procesar</h2>
            @if (count($Activas) > 6)
                <a class="small font-weight-bold" href="{{ route('SolicPendFacturas', ['id' => Crypt::encrypt($div->id)]) }}">Ver los {{ count($Activas) }} <i class="fas fa-arrow-right ml-1"></i></a>
            @endif
        </div>

        @if (count($Activas))
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Proveedor</th>
                            <th scope="col">Remisión / Orden</th>
                            <th scope="col">Ingreso</th>
                            <th scope="col" class="text-right">Valor</th>
                            <th scope="col"><span class="sr-only">Acción</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($Activas->take(6) as $p)
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $p->Proveedor ?? 'Proveedor sin nombre' }}</div>
                                    @if ($p->Nit)<div class="small text-muted">NIT {{ $p->Nit }}</div>@endif
                                </td>
                                <td>
                                    <div>{{ $p->Nfactura }}</div>
                                    <div class="small text-muted">OP {{ $p->OrdenPedido }}</div>
                                </td>
                                <td class="text-nowrap">{{ $p->FechaIngreso ? \Carbon\Carbon::parse($p->FechaIngreso)->locale('es')->isoFormat('D MMM YYYY') : '—' }}</td>
                                <td class="text-right text-nowrap">{{ $dinero($p->PrecioTotal) }}</td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-primary" href="{{ route('AprobarFactura', ['id' => Crypt::encrypt($p->Id), 'div' => $div->id]) }}">
                                        Revisar <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="inv-vacio">
                <i class="fas fa-check-circle" style="color:#15803D"></i>
                <p>No hay pedidos pendientes. Todo lo recibido ya fue procesado.</p>
            </div>
        @endif
    </div>
@endif
@endsection
