@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@push('css')
<style>
    /* Inicio de Inventario: tablero de datos */
    .inv-page .inv-cabecera {
        position: relative; overflow: hidden; gap: 1rem;
        padding: 1.5rem 1.75rem; border-radius: 18px; color: #fff;
        background:
            radial-gradient(circle at 92% 0%, rgba(255, 255, 255, .14) 0, transparent 38%),
            linear-gradient(135deg, #003F63 0%, #005484 55%, #0A6AA0 100%);
        box-shadow: 0 14px 34px rgba(0, 63, 99, .22);
    }
    .inv-page .inv-cabecera h1 { font-size: 1.75rem; font-weight: 800; color: #fff; margin: 0; letter-spacing: -.01em; }
    .inv-page .inv-cabecera p { margin: .2rem 0 0; color: #D5E7F3; }
    .inv-page .inv-cabecera .btn-primary { background: #fff; border-color: #fff; color: #003F63; box-shadow: none; }
    .inv-page .inv-cabecera .btn-primary:hover { background: #E0F2FE; border-color: #E0F2FE; color: #003F63; }
    .inv-page .inv-cabecera .btn-outline-primary { background: transparent; border-color: rgba(255, 255, 255, .55); color: #fff; }
    .inv-page .inv-cabecera .btn-outline-primary:hover { background: rgba(255, 255, 255, .14); border-color: #fff; color: #fff; }

    .inv-page .inv-kpi {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: .75rem;
        padding: 1.1rem 1.2rem;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .inv-page .inv-kpi::before {
        content: "";
        position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
        background: var(--inv-primario);
    }
    .inv-page .inv-kpi.is-critico::before { background: #d03b3b; }
    .inv-page .inv-kpi.is-alerta::before { background: #fab219; }
    .inv-page .inv-kpi-etiqueta { font-size: .85rem; color: var(--inv-texto-2); font-weight: 600; }
    .inv-page .inv-kpi-valor { font-size: 2rem; font-weight: 700; color: var(--inv-texto); line-height: 1.1; }
    .inv-page .inv-kpi-pie { font-size: .8rem; color: var(--inv-texto-2); }
    .inv-page .inv-kpi a { color: inherit; text-decoration: none; }
    .inv-page .inv-kpi a:hover .inv-kpi-valor { text-decoration: underline; }

    .inv-page .inv-tarjeta {
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: .75rem;
        padding: 1.1rem 1.2rem;
        height: 100%;
    }
    .inv-page .inv-tarjeta h2 { font-size: 1.05rem; font-weight: 700; color: var(--inv-texto); margin: 0 0 .75rem; }

    .inv-page .inv-chip {
        display: inline-block; font-size: .75rem; font-weight: 600;
        padding: .2rem .55rem; border-radius: 999px;
    }
    .inv-page .inv-chip-aprobada { background: #DCFCE7; color: #166534; }
    .inv-page .inv-chip-pendiente { background: #FEF3C7; color: #92400E; }

    .inv-page .inv-barra { height: 8px; background: #E5E7EB; border-radius: 4px; overflow: hidden; min-width: 90px; }
    .inv-page .inv-barra > span { display: block; height: 100%; background: var(--inv-primario); }
    .inv-page .inv-barra > span.is-critico { background: #d03b3b; }

    .inv-page .inv-grafico { position: relative; height: 260px; }
    .inv-page .inv-tabla-tarjeta td, .inv-page .inv-tabla-tarjeta th { font-size: .9rem; }
    .inv-page .inv-vacio-texto { color: var(--inv-texto-2); font-size: .9rem; margin: .5rem 0 0; }
</style>
@endpush

@section('content')
    @php
        $fmt = fn ($n) => number_format((int) $n, 0, ',', '.');
    @endphp

    <div class="container-fluid py-3">

        {{-- Cabecera y acciones rápidas --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 inv-cabecera">
            <div>
                <h1>Inventario</h1>
                <p>{{ $nombreDivision ?: 'Sin división asignada' }} · {{ now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}</p>
            </div>
            <div class="inv-acciones inv-acciones--fin mt-2 mt-md-0">
                <a href="{{ route('Crear_SolicProduc') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Nueva solicitud
                </a>
            </div>
        </div>

        @if ($puedeAprobar && $pendientesAprobar > 0)
            <div class="alert inv-alerta-ambar d-flex flex-wrap justify-content-between align-items-center" role="alert">
                <span><i class="fas fa-hourglass-half mr-2"></i>Hay <strong>{{ $fmt($pendientesAprobar) }}</strong> solicitud(es) esperando aprobación.</span>
                <a href="{{ route('Solicitudes') }}" class="btn btn-sm btn-warning font-weight-bold mt-2 mt-md-0">Revisar</a>
            </div>
        @endif

        {{-- Módulos --}}
        <section class="mb-2">
            <h2 class="br-seccion">Mis solicitudes</h2>
            <div class="row">
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <a href="{{ route('Crear_SolicProduc') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-azul"><i class="fas fa-cart-plus"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Crear solicitud</strong>
                                <small>Pedir productos al almacén</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <a href="{{ route('Pendientes') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-ambar"><i class="fas fa-hourglass-half"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Solicitudes pendientes</strong>
                                <small>Esperando aprobación</small>
                            </span>
                            <span class="br-modulo__num{{ $mi['pendientes'] > 0 ? ' is-alerta' : '' }}">{{ $fmt($mi['pendientes']) }}</span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <a href="{{ route('Aprobadas') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-verde"><i class="fas fa-check-circle"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Solicitudes aprobadas</strong>
                                <small>Ya fueron atendidas</small>
                            </span>
                            <span class="br-modulo__num">{{ $fmt($mi['aprobadas']) }}</span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
            </div>
        </section>

        @if ($puedeAprobar)
            <section class="mb-2">
                <h2 class="br-seccion">Solicitudes recibidas</h2>
                <div class="row">
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <a href="{{ route('Solicitudes') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-ambar"><i class="fas fa-inbox"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Por aprobar</strong>
                                <small>Solicitudes de la división</small>
                            </span>
                            <span class="br-modulo__num{{ $pendientesAprobar > 0 ? ' is-alerta' : '' }}">{{ $fmt($pendientesAprobar) }}</span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <a href="{{ route('TotalAprobadas') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-verde"><i class="fas fa-clipboard-check"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Aprobadas</strong>
                                <small>Historial de solicitudes aprobadas</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <a href="{{ route('Inventario') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-azul"><i class="fas fa-boxes"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Inventario</strong>
                                <small>Existencias por producto y ubicación</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                </div>
            </section>

            <section class="mb-3">
                <h2 class="br-seccion">Almacén</h2>
                <div class="row">
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <a href="{{ route('EntAlmacen') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-cian"><i class="fas fa-dolly-flatbed"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Entradas de almacén</strong>
                                <small>Facturas y recepción de compras</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <a href="{{ route('In_Arqueo') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-violeta"><i class="fas fa-clipboard-list"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Arqueo</strong>
                                <small>Conteo físico del inventario</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <a href="{{ route('InicioAlertaStock') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-ambar"><i class="fas fa-exclamation-triangle"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Alertas de stock</strong>
                                <small>Productos bajo el mínimo</small>
                            </span>
                            <span class="br-modulo__num{{ $indicadores['bajo_minimo'] > 0 ? ' is-alerta' : '' }}">{{ $fmt($indicadores['bajo_minimo']) }}</span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                </div>
            </section>

            <h2 class="br-seccion">Resumen de la división</h2>
        @endif

        @if ($puedeAprobar)
        {{-- Indicadores (solo quien aprueba: panorama de la división) --}}
        <div class="row">
            <div class="col-6 col-lg-3 mb-3">
                <div class="inv-kpi">
                    <div class="inv-kpi-etiqueta">Posiciones de inventario</div>
                    <div class="inv-kpi-valor">{{ $fmt($indicadores['posiciones']) }}</div>
                    <div class="inv-kpi-pie">Producto por ubicación en su división</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <div class="inv-kpi">
                    <div class="inv-kpi-etiqueta">Unidades en stock</div>
                    <div class="inv-kpi-valor">{{ $fmt($indicadores['unidades']) }}</div>
                    <div class="inv-kpi-pie">Suma de existencias actuales</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <a href="{{ route('Inventario') }}" class="d-block h-100">
                    <div class="inv-kpi is-critico">
                        <div class="inv-kpi-etiqueta"><i class="fas fa-times-circle mr-1"></i>Sin existencia</div>
                        <div class="inv-kpi-valor">{{ $fmt($indicadores['sin_existencia']) }}</div>
                        <div class="inv-kpi-pie">Posiciones con cero unidades</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-lg-3 mb-3">
                <a href="{{ route('InicioAlertaStock') }}" class="d-block h-100">
                    <div class="inv-kpi is-alerta">
                        <div class="inv-kpi-etiqueta"><i class="fas fa-exclamation-triangle mr-1"></i>Bajo el mínimo</div>
                        <div class="inv-kpi-valor">{{ $fmt($indicadores['bajo_minimo']) }}</div>
                        <div class="inv-kpi-pie">Hay que reponer</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Movimientos y solicitudes --}}
        <div class="row">
            <div class="col-lg-7 mb-3">
                <div class="inv-tarjeta">
                    <h2>Movimientos por mes</h2>
                    <div class="inv-grafico">
                        <canvas id="graficoMovimientos" role="img"
                            aria-label="Gráfico de barras: movimientos registrados por mes en los últimos doce meses"></canvas>
                    </div>
                    <details class="mt-2">
                        <summary class="small text-muted">Ver los datos en tabla</summary>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm inv-tabla-tarjeta mb-0">
                                <thead><tr><th>Mes</th><th class="text-right">Movimientos</th></tr></thead>
                                <tbody>
                                    @foreach ($meses as $m)
                                        <tr><td>{{ $m['etiqueta'] }}</td><td class="text-right">{{ $fmt($m['valor']) }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
            </div>

            <div class="col-lg-5 mb-3">
                <div class="inv-tarjeta">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="mb-0">Solicitudes recientes</h2>
                        <a href="{{ route('Pendientes') }}" class="small">Ver todas</a>
                    </div>
                    @forelse ($solicitudes as $s)
                        <div class="d-flex justify-content-between align-items-center py-2 border-top">
                            <div>
                                <div class="font-weight-bold">#{{ $s['id'] }} · {{ $s['solicitante'] }}</div>
                                <div class="small text-muted">{{ $s['fecha'] }}</div>
                            </div>
                            @if ($s['aprobada'])
                                <span class="inv-chip inv-chip-aprobada"><i class="fas fa-check mr-1"></i>Aprobada</span>
                            @else
                                <span class="inv-chip inv-chip-pendiente"><i class="fas fa-hourglass-half mr-1"></i>Pendiente</span>
                            @endif
                        </div>
                    @empty
                        <p class="inv-vacio-texto">Aún no hay solicitudes en su división.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Reposición y entradas --}}
        <div class="row">
            <div class="col-lg-7 mb-3">
                <div class="inv-tarjeta">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="mb-0">Reponer primero</h2>
                        <a href="{{ route('InicioAlertaStock') }}" class="small">Ver alertas</a>
                    </div>
                    @if (count($reposicion))
                        <div class="table-responsive">
                            <table class="table table-sm inv-tabla-tarjeta mb-0">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-right">Existencia</th>
                                        <th class="text-right">Mínimo</th>
                                        <th style="min-width: 120px">Cobertura</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reposicion as $r)
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold">{{ $r['producto'] }}</div>
                                                <div class="small text-muted">{{ $r['codigo'] }}</div>
                                            </td>
                                            <td class="text-right">{{ $fmt($r['existencia']) }}</td>
                                            <td class="text-right">{{ $fmt($r['minimo']) }}</td>
                                            <td>
                                                <div class="inv-barra" title="{{ $r['cobertura'] }}% del mínimo">
                                                    <span class="{{ $r['existencia'] <= 0 ? 'is-critico' : '' }}" style="width: {{ $r['cobertura'] }}%"></span>
                                                </div>
                                                <div class="small text-muted">
                                                    @if ($r['existencia'] <= 0)
                                                        <strong style="color:#b42318">Sin existencia</strong>
                                                    @else
                                                        Faltan {{ $fmt($r['faltan']) }}
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="inv-vacio-texto"><i class="fas fa-check-circle text-success mr-1"></i>Ningún producto está por debajo de su mínimo.</p>
                    @endif
                </div>
            </div>

            <div class="col-lg-5 mb-3">
                <div class="inv-tarjeta">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="mb-0">Entradas recientes</h2>
                        <a href="{{ route('EntAlmacen') }}" class="small">Ver todas</a>
                    </div>
                    @forelse ($entradas as $e)
                        <div class="d-flex justify-content-between align-items-center py-2 border-top">
                            <div>
                                <div class="font-weight-bold">{{ $e['proveedor'] }}</div>
                                <div class="small text-muted">Factura {{ $e['factura'] }} · {{ $e['fecha'] }}</div>
                            </div>
                            <div class="font-weight-bold text-right">{{ $e['total'] > 0 ? '$' . number_format($e['total'], 0, ',', '.') : '—' }}</div>
                        </div>
                    @empty
                        <p class="inv-vacio-texto">Aún no hay entradas de almacén en su división.</p>
                    @endforelse
                </div>
            </div>
        </div>
        @else
            {{-- Vista personal: solo las solicitudes de esta persona --}}
            <div class="row">
                <div class="col-6 col-lg-3 mb-3">
                    <a href="{{ route('Pendientes') }}" class="d-block h-100">
                        <div class="inv-kpi is-alerta">
                            <div class="inv-kpi-etiqueta"><i class="fas fa-hourglass-half mr-1"></i>Mis solicitudes pendientes</div>
                            <div class="inv-kpi-valor">{{ $mi['pendientes'] }}</div>
                            <div class="inv-kpi-pie">Esperando aprobación</div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <a href="{{ route('Aprobadas') }}" class="d-block h-100">
                        <div class="inv-kpi">
                            <div class="inv-kpi-etiqueta"><i class="fas fa-check-circle mr-1"></i>Mis solicitudes aprobadas</div>
                            <div class="inv-kpi-valor">{{ $mi['aprobadas'] }}</div>
                            <div class="inv-kpi-pie">Ya fueron atendidas</div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-lg-6 mb-3">
                    <div class="inv-tarjeta d-flex flex-column justify-content-center h-100">
                        <h2 class="mb-2">¿Necesita productos?</h2>
                        <p class="inv-subtitulo mb-3">Haga su pedido y le llegará la notificación cuando sea aprobado.</p>
                        <div class="inv-acciones">
                            <a href="{{ route('Crear_SolicProduc') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Nueva solicitud</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <div class="inv-tarjeta">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h2 class="mb-0">Mis solicitudes recientes</h2>
                            <a href="{{ route('Pendientes') }}" class="small">Ver todas</a>
                        </div>
                        @forelse ($mi['recientes'] as $s)
                            <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                <div>
                                    <div class="font-weight-bold">Solicitud #{{ $s['id'] }}</div>
                                    <div class="small text-muted">Pedida el {{ $s['fecha'] }} · entrega tentativa {{ $s['entrega'] }}</div>
                                </div>
                                @if ($s['aprobada'])
                                    <span class="inv-estado inv-estado--ok"><i class="fas fa-check mr-1"></i>Aprobada</span>
                                @else
                                    <span class="inv-estado inv-estado--alerta"><i class="fas fa-hourglass-half mr-1"></i>Pendiente</span>
                                @endif
                            </div>
                        @empty
                            <div class="inv-vacio">
                                <i class="fas fa-clipboard"></i>
                                <p>Todavía no ha hecho solicitudes de productos.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@if ($puedeAprobar)
@push('js')
<script src="{{ asset('assets/lte/plugins/chart.js/Chart.min.js') }}"></script>
<script>
    (function () {
        var datos = @json($meses);
        var lienzo = document.getElementById('graficoMovimientos');
        if (!lienzo || typeof Chart === 'undefined') { return; }

        new Chart(lienzo, {
            type: 'bar',
            data: {
                labels: datos.map(function (d) { return d.etiqueta; }),
                datasets: [{
                    label: 'Movimientos',
                    data: datos.map(function (d) { return d.valor; }),
                    backgroundColor: '#005484',
                    hoverBackgroundColor: '#00426a',
                    borderRadius: 4,
                    maxBarThickness: 36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function (item) {
                            return ' ' + Number(item.yLabel).toLocaleString('es-CO') + ' movimientos';
                        }
                    }
                },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0, fontColor: '#4B5563' }, gridLines: { color: '#E5E7EB' } }],
                    xAxes: [{ ticks: { fontColor: '#4B5563' }, gridLines: { display: false } }]
                }
            }
        });
    })();
</script>
@endpush
@endif
