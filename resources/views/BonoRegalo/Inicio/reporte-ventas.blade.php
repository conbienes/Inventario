@extends("theme.$theme.layout")

@section('content')
    <div class="container-fluid pt-4 pb-2 no-bottom-gap">
        {{-- Header Mejorado --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-header">
                    <h1 class="display-6 fw-bold text-white mb-2">🎁 Dashboard de Bonos Regalo</h1>
                    <p class="text-white-80 mb-0">Visualización en tiempo real de ventas y métricas</p>
                </div>
            </div>
        </div>

        {{-- Filtro Mejorado --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="modern-card p-4">
                    <form id="filtroVentas" method="GET" action="{{ route('BonoRegalo.ReporteVentas') }}" class="row g-3 align-items-end">
                        @php
                            $hoy = now()->format('Y-m-d');
                        @endphp

                        <div class="col-12 col-md-2">
                            <label for="from" class="form-label fw-semibold">📅 Desde</label>
                            <input type="date" id="from" name="from" value="{{ request('from', $from) }}"
                                   class="form-control modern-input" max="{{ $hoy }}">
                        </div>

                        <div class="col-12 col-md-2">
                            <label for="to" class="form-label fw-semibold">📅 Hasta</label>
                            <input type="date" id="to" name="to" value="{{ request('to', $to ) }}"
                                   class="form-control modern-input" max="{{ $hoy }}">
                        </div>

                        <div class="col-12 col-md-8 d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn modern-btn primary">
                                <i class="fas fa-filter me-2"></i>Aplicar Filtros
                            </button>
                            <a href="{{ route('BonoRegalo.ReporteVentas') }}" class="btn modern-btn secondary">
                                <i class="fas fa-eraser me-2"></i>Limpiar
                            </a>
                            <button type="submit" class="btn modern-btn success"
                                    formaction="{{ route('BonoRegalo.exportarFacturas') }}" formmethod="GET">
                                <i class="fas fa-file-excel me-2"></i>Exportar Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- KPIs Mejorados --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-4">
                <div class="kpi-card primary">
                    <div class="kpi-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">TOTAL DINERO</span>
                        <h2 class="kpi-value">${{ number_format($totalVendido, 0, ',', '.') }}</h2>
                        <div class="kpi-period">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $from }} → {{ $to }}
                        </div>
                    </div>
                    <div class="kpi-sparkline"></div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="kpi-card success">
                    <div class="kpi-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">RECIBOS DE CAJA</span>
                        <h2 class="kpi-value">{{ number_format($transacciones, 0, ',', '.') }}</h2>
                        <div class="kpi-period">
                            <i class="fas fa-check-circle me-1"></i>
                            Pagos aprobados
                        </div>
                    </div>
                    <div class="kpi-sparkline"></div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="kpi-card warning">
                    <div class="kpi-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">TICKET PROMEDIO</span>
                        <h2 class="kpi-value">${{ number_format($ticketPromedio, 0, ',', '.') }}</h2>
                        <div class="kpi-period">
                            <i class="fas fa-calculator me-1"></i>
                            Total / Transacciones
                        </div>
                    </div>
                    <div class="kpi-sparkline"></div>
                </div>
            </div>
        </div>

        {{-- Tarjetas Activas vs Vendidas --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="modern-card h-100">
                    <div class="card-header-modern">
                        <div class="d-flex align-items-center">
                            <div class="status-indicator active"></div>
                            <h5 class="mb-0 fw-bold">Tarjetas Activas</h5>
                        </div>
                        <span class="badge-modern success">En circulación</span>
                    </div>
                    <div class="card-body">
                        <div class="table-modern">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Denominación</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tarjetasActivas as $r)
                                        <tr>
                                            <td>
                                                <span class="denomination">
                                                    ${{ number_format($r->valor, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="count">{{ number_format($r->cantidad_tarjetas, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="amount positive">${{ number_format($r->total_valor, 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                                <p class="mb-0">Sin tarjetas activas</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-footer-modern">
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-end">{{ number_format($totActCant ?? 0, 0, ',', '.') }}</th>
                                        <th class="text-end">${{ number_format($totActVal ?? 0, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="modern-card h-100">
                    <div class="card-header-modern">
                        <div class="d-flex align-items-center">
                            <div class="status-indicator sold"></div>
                            <h5 class="mb-0 fw-bold">Bonos Regalos Vendidos</h5>
                        </div>
                        <span class="badge-modern primary">Compradas</span>
                    </div>
                    <div class="card-body">
                        <div class="table-modern">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Denominación</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tarjetasInactivas as $r)
                                        <tr>
                                            <td>
                                                <span class="denomination">
                                                    ${{ number_format($r->valor, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="count">{{ number_format($r->cantidad_tarjetas, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="amount primary">${{ number_format($r->total_valor, 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">
                                                <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                                                <p class="mb-0">Sin tarjetas vendidas</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-footer-modern">
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-end">{{ number_format($totInaCant ?? 0, 0, ',', '.') }}</th>
                                        <th class="text-end">${{ number_format($totInaVal ?? 0, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráficos --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-8">
                <div class="modern-card">
                    <div class="card-header-modern">
                        <h5 class="mb-0 fw-bold">Ventas por Hora</h5>
                        @if (isset($horaPico))
                            <span class="badge-modern warning">Hora pico: {{ $horaPico }}:00</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="chart-box"><canvas id="chartHora"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="modern-card">
                    <div class="card-header-modern">
                        <h5 class="mb-0 fw-bold">Ventas por Día</h5>
                        @if (isset($diaPico))
                            <span class="badge-modern warning">Día pico: {{ $diaPico }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="chart-box-sm"><canvas id="chartDia"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Línea y Dona --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-8">
                <div class="modern-card">
                    <div class="card-header-modern">
                        <h5 class="mb-0 fw-bold">Tendencia Diaria</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-box"><canvas id="chartLinea"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="modern-card">
                    <div class="card-header-modern">
                        <h5 class="mb-0 fw-bold">Métodos de Pago</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-box-sm"><canvas id="chartDonut"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tablas Finales --}}
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="modern-card">
                    <div class="card-header-modern">
                        <h5 class="mb-0 fw-bold">Detalle por Método</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-modern">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Método</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end"># Transacciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($porMetodo as $row)
                                        <tr>
                                            <td>{{ $row->metodo }}</td>
                                            <td class="text-end fw-bold">${{ number_format($row->total, 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($row->n, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Sin datos en el rango</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="modern-card">
                    <div class="card-header-modern">
                        <h5 class="mb-0 fw-bold">Últimos Pagos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-modern">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Método</th>
                                        <th>Factura</th>
                                        <th class="text-end">Valor</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($ultimas as $p)
                                        <tr>
                                            <td>{{ $p->created_at?->format('Y-m-d H:i') }}</td>
                                            <td>{{ $p->method->name ?? 'N/D' }}</td>
                                            <td>
                                                @if ($p->invoice)
                                                    <span class="invoice-badge">{{ $p->invoice->numero_factura ?? $p->invoice->id }}</span>
                                                @else
                                                    N/D
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold">${{ number_format($p->amount, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="status-badge {{ in_array($p->status, ['approved', 'paid', 'success', 'completed']) ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($p->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Sin movimientos recientes</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estilos Mejorados --}}
    <style>
        :root {
            --primary-color: #005484;
            --primary-light: #0066a6;
            --primary-dark: #003d63;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --secondary-color: #6c757d;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 84, 132, 0.15);
            --box-shadow-hover: 0 8px 30px rgba(0, 84, 132, 0.25);
            --transition: all 0.3s ease;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            padding: 2rem;
            border-radius: var(--border-radius);
            position: relative;
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .text-white-80 {
            color: rgba(255,255,255,0.8) !important;
        }

        .modern-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(0, 84, 132, 0.1);
            transition: var(--transition);
            overflow: hidden;
        }

        .modern-card:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-2px);
        }

        .card-header-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header-modern h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .modern-input {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .modern-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 84, 132, 0.1);
        }

        .modern-btn {
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .modern-btn.primary {
            background: var(--primary-color);
            color: white;
        }

        .modern-btn.secondary {
            background: var(--secondary-color);
            color: white;
        }

        .modern-btn.success {
            background: var(--success-color);
            color: white;
        }

        .modern-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* KPI Cards */
        .kpi-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
            position: relative;
            overflow: hidden;
        }

        .kpi-card.primary { border-left-color: var(--primary-color); }
        .kpi-card.success { border-left-color: var(--success-color); }
        .kpi-card.warning { border-left-color: var(--warning-color); }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-hover);
        }

        .kpi-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .kpi-card.primary .kpi-icon { background: var(--primary-color); }
        .kpi-card.success .kpi-icon { background: var(--success-color); }
        .kpi-card.warning .kpi-icon { background: var(--warning-color); }

        .kpi-content {
            flex: 1;
        }

        .kpi-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            display: block;
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin: 0;
            line-height: 1;
        }

        .kpi-period {
            font-size: 0.875rem;
            color: var(--secondary-color);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
        }

        .kpi-sparkline {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
            animation: sparkle 2s infinite;
        }

        /* Table Styles */
        .table-modern {
            margin: 0;
        }

        .table-modern .table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 700;
            color: var(--primary-dark);
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
            padding: 1rem 0.75rem;
        }

        .table-modern .table tbody td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        .table-modern .table tbody tr:hover {
            background: #f8f9fa;
        }

        .denomination {
            background: #e9ecef;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 0.9rem;
        }

        .count {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .amount {
            font-weight: 700;
            font-size: 1rem;
        }

        .amount.positive { color: var(--success-color); }
        .amount.primary { color: var(--primary-color); }

        .table-footer-modern {
            background: #f8f9fa;
            font-weight: 700;
        }

        .table-footer-modern th {
            border-top: 2px solid #dee2e6 !important;
            color: var(--primary-dark);
        }

        /* Status Elements */
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 0.75rem;
        }

        .status-indicator.active { background: var(--success-color); }
        .status-indicator.sold { background: var(--primary-color); }

        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .badge-modern.success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .badge-modern.primary {
            background: rgba(0, 84, 132, 0.1);
            color: var(--primary-color);
            border: 1px solid rgba(0, 84, 132, 0.2);
        }

        .badge-modern.warning {
            background: rgba(255, 193, 7, 0.1);
            color: #b58a00;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }

        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.success {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .status-badge.secondary {
            background: rgba(108, 117, 125, 0.1);
            color: var(--secondary-color);
        }

        .invoice-badge {
            background: rgba(0, 84, 132, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            color: var(--primary-color);
            font-weight: 500;
        }

        /* Chart Containers */
        .chart-box {
            position: relative;
            width: 100%;
            height: 320px;
        }

        .chart-box-sm {
            position: relative;
            width: 100%;
            height: 300px;
        }

        .chart-box canvas,
        .chart-box-sm canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        @keyframes sparkle {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1.5rem;
            }

            .kpi-card {
                padding: 1.5rem;
                gap: 1rem;
            }

            .kpi-value {
                font-size: 1.5rem;
            }

            .kpi-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .chart-box,
            .chart-box-sm {
                height: 250px;
            }

            .card-header-modern {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .card-header-modern h5 {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .chart-box,
            .chart-box-sm {
                height: 220px;
            }
        }
    </style>
@endsection

{{-- Scripts (mantenemos tus scripts existentes) --}}
<script>
    (function() {
        const $from = document.getElementById('from');
        const $to = document.getElementById('to');

        function clampDates() {
            if ($from.value && $to.value && $from.value > $to.value) $to.value = $from.value;
        }
        $from?.addEventListener('change', clampDates);
        $to?.addEventListener('change', clampDates);
    })();
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function() {
        const labelsBarHora = @json($labelsBarHora ?? []);
        const dataBarHora = @json($dataBarHora ?? []);
        const labelsBarDia = @json($labelsBarDia ?? []);
        const dataBarDia = @json($dataBarDia ?? []);
        const labelsLinea = @json($labelsLinea ?? []);
        const dataLinea = @json($dataLinea ?? []);
        const labelsDonut = @json($labelsDonut ?? []);
        const dataDonut = @json($dataDonut ?? []);

        function ready(cb) {
            document.readyState !== 'loading' ? cb() : document.addEventListener('DOMContentLoaded', cb);
        }

        function nf(v) {
            return new Intl.NumberFormat('es-CO').format(v);
        }

        ready(() => {
            if (!window.Chart) return;

            // Línea
            const elLine = document.getElementById('chartLinea');
            if (elLine) {
                new Chart(elLine.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labelsLinea,
                        datasets: [{
                            label: 'Ventas diarias',
                            data: dataLinea,
                            tension: .25,
                            fill: true,
                            pointRadius: 2,
                            borderColor: '#005484',
                            backgroundColor: 'rgba(0, 84, 132, 0.1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => nf(v)
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => '$ ' + nf(ctx.parsed.y)
                                }
                            }
                        }
                    }
                });
            }

            // Donut
            const elDonut = document.getElementById('chartDonut');
            if (elDonut) {
                new Chart(elDonut.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: labelsDonut,
                        datasets: [{
                            data: dataDonut,
                            backgroundColor: [
                                '#005484',
                                '#28a745',
                                '#ffc107',
                                '#dc3545',
                                '#6c757d',
                                '#17a2b8'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => `${ctx.label}: $ ${nf(ctx.raw)}`
                                }
                            }
                        }
                    }
                });
            }

            // Barra por hora
            const elHora = document.getElementById('chartHora');
            if (elHora) {
                new Chart(elHora.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labelsBarHora,
                        datasets: [{
                            label: 'Total vendido',
                            data: dataBarHora,
                            backgroundColor: '#005484'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Hora del día'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => nf(v)
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => '$ ' + nf(ctx.parsed.y)
                                }
                            }
                        }
                    }
                });
            }

            // Barra por día
            const elDia = document.getElementById('chartDia');
            if (elDia) {
                new Chart(elDia.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labelsBarDia,
                        datasets: [{
                            label: 'Total vendido',
                            data: dataBarDia,
                            backgroundColor: '#005484'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => nf(v)
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => '$ ' + nf(ctx.parsed.y)
                                }
                            }
                        }
                    }
                });
            }
        });
    })();
</script>
