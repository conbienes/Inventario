@extends("theme.$theme.layout")

@section('content')
    <div class="container-fluid px-2 py-3 contabilidad-compact">
        {{-- Header Compacto --}}
        <div class="row mb-1">
            <div class="col-12">
                <div class="dashboard-header-compact">
                    <div class="header-content-compact">
                        <h1 class="h3 fw-bold text-white mb-1">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Módulo Contabilidad
                        </h1>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros Compactos --}}
        <form method="GET" action="{{ route('BonoRegalo.Contabilidad') }}" id="filtrosForm"
            class="modern-filter-compact mb-3 position-relative">
            {{-- Overlay Cargando --}}
            <div id="fltLoading" class="flt-overlay-compact d-none">
                <div class="spinner-compact"></div>
            </div>

            <div class="filter-header-compact">
                <div class="filter-title-compact">
                    <i class="fas fa-sliders-h me-2"></i>
                    <span class="fw-semibold">Filtros</span>
                </div>
                <div class="filter-actions-compact">
                    <button type="submit" class="btn-compact primary">
                        <i class="fas fa-filter me-1"></i>Aplicar
                    </button>
                    <a href="{{ route('BonoRegalo.Contabilidad') }}" class="btn-compact primary">
                        <i class="fas fa-eraser me-1"></i>Limpiar
                    </a>
                </div>
            </div>

            <div class="filter-body-compact">
                <div class="row g-2 align-items-end">
                    {{-- Buscar --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label-compact">
                            <i class="fas fa-search me-1"></i>Buscar
                        </label>
                        <div class="input-group-compact">
                            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control-compact"
                                placeholder="N° factura o cliente" autocomplete="off" id="flt_q">
                            <span class="input-icon-compact"><i class="fas fa-search"></i></span>
                        </div>
                    </div>

                    {{-- Fechas --}}
                    <div class="col-6 col-md-2">
                        <label class="form-label-compact">
                            <i class="fas fa-calendar-alt me-1"></i>Desde
                        </label>
                        <input type="date" name="desde" value="{{ $desde ?? '' }}" class="form-control-compact"
                            max="{{ now()->toDateString() }}" id="flt_from">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label-compact">
                            <i class="fas fa-calendar-alt me-1"></i>Hasta
                        </label>
                        <input type="date" name="hasta" value="{{ $hasta ?? '' }}" class="form-control-compact"
                            max="{{ now()->toDateString() }}" id="flt_to">
                    </div>

                    {{-- Rangos rápidos --}}
                    <div class="col-12 col-md-5">
                        <label class="form-label-compact">
                            <i class="fas fa-bolt me-1"></i>Rango Rápido
                        </label>
                        <div class="quick-range-compact">
                            <button class="btn-compact quick-range-btn" type="button" data-range="hoy">
                                Hoy
                            </button>
                            <button class="btn-compact quick-range-btn" type="button" data-range="7">
                                7 días
                            </button>
                            <button class="btn-compact quick-range-btn" type="button" data-range="30">
                                30 días
                            </button>
                            <button class="btn-compact quick-range-btn" type="button" data-range="mes">
                                Este mes
                            </button>
                        </div>
                    </div>

                    {{-- Estado BC --}}
                    <div class="col-12">
                        <label class="form-label-compact">
                            <i class="fas fa-tags me-1"></i>Estado BC
                        </label>

                        @php
                            $allEstados = $allEstados ?? ['ENVIADO', 'ERROR', 'PENDIENTE'];
                            $estadosSel = (array) ($estados ?? $allEstados);
                            $todosMarcados = empty(array_diff($allEstados, $estadosSel));
                        @endphp

                        <div class="estado-chips-compact">
                            <label class="chip-compact chip-all">
                                <input type="checkbox" id="chk_all" {{ $todosMarcados ? 'checked' : '' }}>
                                <span class="chip-pill-compact">
                                    <i class="fas fa-layer-group me-1"></i>Todos
                                </span>
                            </label>

                            @foreach ($allEstados as $e)
                                <label class="chip-compact" data-e="{{ $e }}">
                                    <input type="checkbox" name="estado[]" value="{{ $e }}" class="chk_estado"
                                        {{ in_array($e, $estadosSel) ? 'checked' : '' }}>
                                    <span class="chip-pill-compact estado-{{ strtolower($e) }}">
                                        @if ($e === 'ENVIADO')
                                            <i class="fas fa-paper-plane me-1"></i>
                                        @elseif($e === 'ERROR')
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                        @elseif($e === 'PENDIENTE')
                                            <i class="fas fa-hourglass-half me-1"></i>
                                        @endif
                                        {{ $e }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Resultado de carga a BC Compacto --}}
        <div id="bcResults" class="modern-results-compact mb-3 d-none position-relative">
            <div id="bcLoading" class="flt-overlay-compact d-none">
                <div class="spinner-compact"></div>
            </div>

            <div class="results-header-compact">
                <div class="results-title-compact">
                    <i class="fas fa-cloud-upload-alt me-2"></i>
                    <span class="fw-semibold">Resultado Carga BC</span>
                </div>
                <button id="bcHide" type="button" class="btn-compact outline btn-xs">
                    <i class="fas fa-times me-1"></i>Ocultar
                </button>
            </div>

            <div class="results-body-compact">
                <div class="results-stats-compact">
                    <div class="stat-item-compact">
                        <div class="stat-icon-compact processed">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="stat-content-compact">
                            <div class="stat-value-compact" id="bcProcessed">0</div>
                            <div class="stat-label-compact">Procesadas</div>
                        </div>
                    </div>
                    <div class="stat-item-compact">
                        <div class="stat-icon-compact success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content-compact">
                            <div class="stat-value-compact text-success" id="bcOk">0</div>
                            <div class="stat-label-compact">Enviadas</div>
                        </div>
                    </div>
                    <div class="stat-item-compact">
                        <div class="stat-icon-compact error">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content-compact">
                            <div class="stat-value-compact text-danger" id="bcErr">0</div>
                            <div class="stat-label-compact">Errores</div>
                        </div>
                    </div>
                    <div class="stat-item-compact">
                        <div class="stat-icon-compact warning">
                            <i class="fas fa-search-minus"></i>
                        </div>
                        <div class="stat-content-compact">
                            <div class="stat-value-compact text-warning" id="bcMiss">0</div>
                            <div class="stat-label-compact">No Encontradas</div>
                        </div>
                    </div>
                </div>
                <div class="results-summary-compact">
                    <span id="bcSummaryText" class="summary-text-compact"></span>
                </div>
            </div>
        </div>

        {{-- Barra de acciones masivas Compacta --}}
        <form id="bulkForm" method="POST" action="{{ route('BonoRegalo.CargarBC') }}"
            class="modern-actions-compact mb-3">
            @csrf
            <div class="actions-header-compact">
                <div class="actions-title-compact">
                    <i class="fas fa-cogs me-2"></i>
                    <span class="fw-semibold">Acciones Masivas</span>
                </div>
                <div class="actions-info-compact">
                    <div class="table-title-compact">
                        <i class="fas fa-check-square me-1"></i>
                        <span id="selCount">0 </span> seleccionadas
                    </div>
                </div>
            </div>

            <div class="actions-body-compact">
                <div class="actions-buttons-compact">
                    <button type="submit" id="bulkApply" class="btn-compact primary" disabled>
                        <i class="fas fa-play me-1"></i>Aplicar
                    </button>
                    <button type="button" id="bulkClear" class="btn-compact outline" disabled>
                        <i class="fas fa-times me-1"></i>Limpiar
                    </button>
                    <button type="button" id="btnLoadClients" class="btn-compact success">
                        <i class="fas fa-user-plus me-1"></i>Clientes
                    </button>
                </div>
            </div>
        </form>

        {{-- Tabla Compacta --}}
        <div class="modern-table-compact">
            <div class="table-header-compact">
                <div class="table-title-compact">
                    <i class="fas fa-table me-2"></i>
                    <span class="fw-semibold">Lista de Facturas</span>
                </div>
                <div class="table-info-compact">
                    <span class="table-count-compact">{{ $facturas->total() }} registros</span>
                </div>
            </div>

            <div class="table-container-compact" id="tablaWrap">
                <table class="modern-table-compact">
                    <thead class="table-head-compact">
                        <tr>
                            <th class="table-checkbox-compact">
                                <input type="checkbox" id="checkAll" class="modern-checkbox-compact">
                            </th>
                            <th class="table-number-compact"># Factura</th>
                            <th class="table-date-compact">Fecha</th>
                            <th class="table-amount-compact">Total</th>
                            <th class="table-payment-compact">Pagado</th>
                            <th class="table-status-compact">Estado BC</th>
                            <th class="table-progress-compact">Progreso</th>
                        </tr>
                    </thead>
                    <tbody id="tablaFacturas" class="table-body-compact">
                        @forelse ($facturas as $f)
                            @php
                                $pagado = (float) ($f->pagado ?? 0);
                                $total = (float) $f->total;
                                $saldo = max(0, $total - $pagado);
                                $pct = $total > 0 ? round(($pagado * 100) / $total) : 0;
                            @endphp
                            <tr class="table-row-compact js-row" data-id="{{ $f->id }}">
                                <td class="table-checkbox-compact">
                                    <input type="checkbox" name="ids[]" value="{{ $f->id }}"
                                        class="ck-item modern-checkbox-compact">
                                </td>
                                <td class="table-number-compact">
                                    <span class="invoice-number-compact">{{ $f->numero_factura ?? $f->id }}</span>
                                </td>
                                <td class="table-date-compact">
                                    <span
                                        class="date-text-compact">{{ \Illuminate\Support\Carbon::parse($f->fecha)->format('Y-m-d') }}</span>
                                </td>
                                <td class="table-amount-compact">
                                    <span class="amount-value-compact">${{ number_format($total, 0, ',', '.') }}</span>
                                </td>
                                <td class="table-payment-compact">
                                    <div class="payment-info-compact">
                                        <span
                                            class="payment-amount-compact">${{ number_format($pagado, 0, ',', '.') }}</span>
                                        <div class="payment-progress-compact">
                                            <div class="progress-track-compact">
                                                <div class="progress-fill-compact {{ $pct >= 100 ? 'complete' : 'pending' }}"
                                                    style="width: {{ $pct }}%;"
                                                    data-progress="{{ $pct }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-status-compact">
                                    <span class="status-badge-compact status-{{ strtolower($f->estado_bc) }}">
                                        @if ($f->estado_bc === 'PENDIENTE')
                                            <i class="fas fa-hourglass-half me-1"></i>
                                        @elseif($f->estado_bc === 'ENVIADO')
                                            <i class="fas fa-paper-plane me-1"></i>
                                        @elseif($f->estado_bc === 'ERROR')
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                        @endif
                                        {{ $f->estado_bc }}
                                    </span>
                                </td>
                                <td class="table-progress-compact">
                                    <span class="progress-text-compact {{ $pct >= 100 ? 'text-success' : 'text-info' }}">
                                        {{ $pct }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr class="table-row-compact empty-row">
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty-state-compact">
                                        <i class="fas fa-inbox fa-2x mb-2 text-muted"></i>
                                        <p class="text-muted mb-0 small">No se encontraron facturas</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($facturas->hasPages())
                <div class="table-footer-compact">
                    <div class="pagination-container-compact">
                        {{ $facturas->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Estilos Compactos --}}
    <style>
        :root {
            --primary-color: #005484;
            --primary-light: #0066a6;
            --primary-dark: #003d63;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --secondary-color: #6b7280;
            --background-light: #f8fafc;
            --border-light: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --border-radius: 8px;
            --border-radius-sm: 6px;
            --transition: all 0.2s ease;
        }

        .contabilidad-compact {
            background: #f8fafc;
            min-height: 100vh;
        }

        /* Header Compacto */
        .dashboard-header-compact {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            padding: 1.25rem 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            margin-bottom: 1rem;
        }

        .header-content-compact h1 {
            font-size: 1.4rem;
            margin-bottom: 0.25rem;
        }

        .text-white-80 {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        /* Cards Compactas */
        .modern-filter-compact,
        .modern-results-compact,
        .modern-actions-compact,
        .modern-table-compact {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1rem;
        }

        /* Headers Compactos */
        .filter-header-compact,
        .results-header-compact,
        .actions-header-compact,
        .table-header-compact {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-title-compact,
        .results-title-compact,
        .actions-title-compact,
        .table-title-compact {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        /* Bodies Compactos */
        .filter-body-compact {
            padding: 1rem;
        }

        .results-body-compact,
        .actions-body-compact {
            padding: 0.75rem 1rem;
        }

        /* Form Elements Compactos */
        .form-label-compact {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            display: block;
            font-size: 0.8rem;
        }

        .form-control-compact {
            border: 1px solid var(--border-light);
            border-radius: var(--border-radius-sm);
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            transition: var(--transition);
            background: white;
            width: 100%;
            height: 2rem;
        }

        .form-control-compact:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0, 84, 132, 0.1);
            outline: none;
        }

        .input-group-compact {
            position: relative;
        }

        .input-group-compact .input-icon-compact {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 0.7rem;
        }

        /* Botones Compactos */
        .btn-compact {
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 0.4rem 0.75rem;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            height: 1.8rem;
        }

        .btn-compact.primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-compact.success {
            background: var(--success-color);
            color: white;
        }

        .btn-compact.outline {
            background: transparent;
            border: 1px solid var(--border-light);
            color: var(--text-secondary);
        }

        .btn-compact:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .btn-compact:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-compact.btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.7rem;
            height: 1.5rem;
        }

        /* Quick Range Compacto */
        .quick-range-compact {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .quick-range-btn {
            padding: 0.3rem 0.6rem;
            font-size: 0.7rem;
        }

        /* Chips Compactos */
        .estado-chips-compact {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .chip-compact {
            cursor: pointer;
            user-select: none;
        }

        .chip-compact input {
            display: none;
        }

        .chip-pill-compact {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.75rem;
            border: 1px solid var(--border-light);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            background: white;
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .chip-pill-compact:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .estado-enviado {
            border-color: rgba(6, 182, 212, 0.3);
            background: rgba(6, 182, 212, 0.05);
            color: #0e7490;
        }

        .estado-error {
            border-color: rgba(239, 68, 68, 0.3);
            background: rgba(239, 68, 68, 0.05);
            color: #dc2626;
        }

        .estado-pendiente {
            border-color: rgba(245, 158, 11, 0.3);
            background: rgba(245, 158, 11, 0.05);
            color: #d97706;
        }

        .chip-compact[data-e="ENVIADO"] input:checked+.chip-pill-compact {
            background: var(--info-color);
            border-color: var(--info-color);
            color: white;
        }

        .chip-compact[data-e="ERROR"] input:checked+.chip-pill-compact {
            background: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }

        .chip-compact[data-e="PENDIENTE"] input:checked+.chip-pill-compact {
            background: var(--warning-color);
            border-color: var(--warning-color);
            color: #1e293b;
        }

        .chip-all input:checked+.chip-pill-compact {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        /* Results Compacto */
        .results-stats-compact {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .stat-item-compact {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: var(--background-light);
            border-radius: var(--border-radius-sm);
        }

        .stat-icon-compact {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: white;
        }

        .stat-icon-compact.processed {
            background: var(--secondary-color);
        }

        .stat-icon-compact.success {
            background: var(--success-color);
        }

        .stat-icon-compact.error {
            background: var(--danger-color);
        }

        .stat-icon-compact.warning {
            background: var(--warning-color);
        }

        .stat-content-compact {
            flex: 1;
        }

        .stat-value-compact {
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.1rem;
        }

        .stat-label-compact {
            font-size: 0.65rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .summary-text-compact {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-align: center;
            display: block;
        }

        /* Actions Bar Compacta */
        .selection-badge-compact {
            background: rgba(0, 84, 132, 0.1);
            color: var(--primary-color);
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
        }

        .actions-buttons-compact {
            display: flex;
            gap: 0.5rem;
        }

        /* Tabla Compacta */
        .table-container-compact {
            max-height: 50vh;
            overflow: auto;
            position: relative;
        }

        .modern-table-compact {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 0.8rem;
        }

        .table-head-compact {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f1f5f9;
        }

        .table-head-compact th {
            padding: 0.6rem 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.3px;
            border-bottom: 1px solid var(--border-light);
            text-align: left;
            white-space: nowrap;
        }

        .table-body-compact .table-row-compact {
            transition: var(--transition);
            border-bottom: 1px solid #f1f5f9;
        }

        .table-body-compact .table-row-compact:hover {
            background: #f8fafc;
        }

        .table-body-compact .table-row-compact.row-selected {
            background: #eef2ff;
            box-shadow: inset 2px 0 0 var(--primary-color);
        }

        .table-body-compact td {
            padding: 0.6rem 0.5rem;
            vertical-align: middle;
        }

        /* Columnas específicas compactas */
        .table-checkbox-compact {
            width: 30px;
            text-align: center;
        }

        .table-number-compact {
            width: 100px;
        }

        .table-date-compact {
            width: 90px;
        }

        .table-amount-compact {
            width: 100px;
            text-align: right;
        }

        .table-payment-compact {
            width: 120px;
        }

        .table-status-compact {
            width: 110px;
        }

        .table-progress-compact {
            width: 60px;
            text-align: center;
        }

        /* Elementos de tabla compactos */
        .modern-checkbox-compact {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            border: 1px solid var(--border-light);
            cursor: pointer;
            transition: var(--transition);
        }

        .modern-checkbox-compact:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .invoice-number-compact {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.75rem;
        }

        .amount-value-compact {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.75rem;
        }

        .payment-info-compact {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .payment-amount-compact {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.75rem;
        }

        .payment-progress-compact {
            display: flex;
            align-items: center;
        }

        .progress-track-compact {
            flex: 1;
            height: 4px;
            background: var(--border-light);
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill-compact {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .progress-fill-compact.pending {
            background: var(--info-color);
        }

        .progress-fill-compact.complete {
            background: var(--success-color);
        }

        .status-badge-compact {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-pendiente {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status-enviado {
            background: rgba(6, 182, 212, 0.1);
            color: #0e7490;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }

        .status-error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .progress-text-compact {
            font-weight: 600;
            font-size: 0.7rem;
        }

        /* Empty State Compacto */
        .empty-state-compact {
            padding: 1.5rem 1rem;
            text-align: center;
        }

        /* Table Footer Compacto */
        .table-footer-compact {
            padding: 0.75rem 1rem;
            background: var(--background-light);
            border-top: 1px solid var(--border-light);
        }

        .pagination-container-compact {
            display: flex;
            justify-content: center;
        }

        .table-count-compact {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Loading Overlay Compacto */
        .flt-overlay-compact {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.9);
            z-index: 20;
            border-radius: var(--border-radius);
        }

        .spinner-compact {
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-light);
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Responsive para móviles */
        @media (max-width: 768px) {
            .dashboard-header-compact {
                padding: 1rem;
            }

            .filter-header-compact,
            .results-header-compact,
            .actions-header-compact,
            .table-header-compact {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }

            .results-stats-compact {
                grid-template-columns: repeat(2, 1fr);
            }

            .actions-buttons-compact {
                flex-direction: column;
                width: 100%;
            }

            .btn-compact {
                width: 100%;
                justify-content: center;
            }

            .table-container-compact {
                max-height: 40vh;
            }

            .stat-value-compact {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .filter-body-compact {
                padding: 0.75rem;
            }

            .table-body-compact td {
                padding: 0.5rem 0.25rem;
            }

            .estado-chips-compact {
                gap: 0.25rem;
            }

            .chip-pill-compact {
                padding: 0.3rem 0.5rem;
                font-size: 0.65rem;
            }
        }
    </style>

    {{-- Mantenemos todos tus scripts existentes --}}
    <script>
        (function() {
            const wrap = document.getElementById('tablaWrap');
            if (!wrap) return;

            const footer = document.querySelector('.main-footer');
            const GAP = 8;

            function ajustarAlto() {
                const rect = wrap.getBoundingClientRect();
                const hFooter = footer ? footer.offsetHeight : 0;
                const disponible = Math.max(200, window.innerHeight - rect.top - hFooter - GAP);

                wrap.style.maxHeight = disponible + 'px';
                wrap.style.overflow = 'auto';
            }

            window.addEventListener('resize', ajustarAlto);
            document.addEventListener('DOMContentLoaded', ajustarAlto);
            setTimeout(ajustarAlto, 0);
        })();
    </script>

    {{-- Todos los demás scripts se mantienen igual --}}
    <script>
        (function() {
            const $ = (s, c = document) => c.querySelector(s);

            const btnLoad = $('#btnLoadClients');
            const resCard = $('#bcResults');
            const resLoad = $('#bcLoading');
            const resProcessed = $('#bcProcessed');
            const resOk = $('#bcOk');
            const resErr = $('#bcErr');
            const resMiss = $('#bcMiss');
            const resSummary = $('#bcSummaryText');

            const setCardVisible = v => resCard.classList.toggle('d-none', !v);
            const setLoading = v => resLoad.classList.toggle('d-none', !v);

            btnLoad?.addEventListener('click', async () => {
                setCardVisible(true);
                setLoading(true);

                try {
                    const resp = await fetch(`{{ route('BonoRegalo.CargarClientes') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            limit: 200
                        })
                    });

                    const json = await resp.json();
                    const processed = Number(json?.processed || 0);
                    const ok = Number(json?.ok || 0);
                    const errors = Number(json?.errors || 0);

                    resProcessed.textContent = processed;
                    resOk.textContent = ok;
                    resErr.textContent = errors;
                    resMiss.textContent = 0;

                    resSummary.textContent =
                        `Clientes creados/actualizados: ${ok} · Errores: ${errors} · Lote procesado: ${processed}`;

                    if (!resp.ok) {
                        console.error('HTTP', resp.status, json);
                        alert('Ocurrió un error al cargar clientes. Revisa el resumen.');
                    }
                } catch (e) {
                    console.error(e);
                    resProcessed.textContent = '0';
                    resOk.textContent = '0';
                    resErr.textContent = '0';
                    resMiss.textContent = '0';
                    resSummary.textContent = 'Error de red o servidor.';
                } finally {
                    setLoading(false);
                }
            });
        })();
    </script>

    <script>
        (function() {
            const $ = (s, c = document) => c.querySelector(s);
            const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

            const form = $('#filtrosForm');
            const loading = $('#fltLoading');
            const qInput = $('#flt_q');
            const fromI = $('#flt_from');
            const toI = $('#flt_to');
            const chkAll = $('#chk_all');
            const chks = $$('.chk_estado');
            const chipLabs = $$('.estado-chips-compact .chip-compact[data-e]');
            const rangeBtns = $$('.quick-range-compact [data-range]');

            function showLoading(v) {
                if (!loading) return;
                loading.classList.toggle('d-none', !v);
            }

            function autoSubmit() {
                showLoading(true);
                form.submit();
            }

            function clampDates() {
                if (fromI.value && toI.value && fromI.value > toI.value) toI.value = fromI.value;
            }

            let t = null;
            qInput?.addEventListener('input', () => {
                clearTimeout(t);
                t = setTimeout(autoSubmit, 500);
            });

            [fromI, toI].forEach(el => el?.addEventListener('change', () => {
                clampDates();
                autoSubmit();
            }));

            function setRange(tipo) {
                const end = new Date();
                let start = new Date(end);
                if (tipo === 'hoy') {
                    /* mismo día */
                } else if (tipo === '7') {
                    start.setDate(end.getDate() - 6);
                } else if (tipo === '30') {
                    start.setDate(end.getDate() - 29);
                } else if (tipo === 'mes') {
                    start = new Date(end.getFullYear(), end.getMonth(), 1);
                }

                const iso = d => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
                fromI.value = iso(start);
                toI.value = iso(end);
                clampDates();
            }

            rangeBtns.forEach(b => b.addEventListener('click', () => {
                setRange(b.dataset.range);
                autoSubmit();
            }));

            chkAll?.addEventListener('change', () => {
                chks.forEach(c => c.checked = chkAll.checked);
                autoSubmit();
            });

            chks.forEach(c => c.addEventListener('change', () => {
                const on = chks.filter(i => i.checked).length;
                chkAll.checked = (on === chks.length);
                autoSubmit();
            }));

            chipLabs.forEach(lab => {
                lab.addEventListener('dblclick', () => {
                    const val = lab.getAttribute('data-e');
                    chks.forEach(c => c.checked = (c.value === val));
                    chkAll.checked = false;
                    autoSubmit();
                });
            });

            form.addEventListener('submit', (e) => {
                const any = chks.some(i => i.checked);
                if (!any) {
                    chks.forEach(i => i.checked = true);
                    chkAll.checked = true;
                }
            });
        })();
    </script>

    <script>
        (function() {
            const $ = (s, c = document) => c.querySelector(s);
            const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

            const bulkForm = $('#bulkForm');
            const items = $$('#tablaFacturas .ck-item');
            const resCard = $('#bcResults');
            const resLoad = $('#bcLoading');
            const resProcessed = $('#bcProcessed');
            const resOk = $('#bcOk');
            const resErr = $('#bcErr');
            const resMiss = $('#bcMiss');
            const resSummary = $('#bcSummaryText');
            const btnHide = $('#bcHide');

            const fmt = n => new Intl.NumberFormat('es-CO', {
                maximumFractionDigits: 0
            }).format(n);

            function setCardVisible(v) {
                resCard.classList.toggle('d-none', !v);
            }

            function setLoading(v) {
                resLoad.classList.toggle('d-none', !v);
            }

            function renderSummary(json) {
                setCardVisible(true);

                const results = json?.results || [];
                const processed = Number(json?.processed ?? results.length ?? 0);
                const okCount = results.filter(r => (r?.estado_bc === 'ENVIADO')).length;
                const errCount = results.filter(r => (r?.estado_bc === 'ERROR')).length;
                const missing = (json?.missing_ids || []).length;

                resProcessed.textContent = processed;
                resOk.textContent = okCount;
                resErr.textContent = errCount;
                resMiss.textContent = missing;

                const totalValor = results.reduce((acc, r) => acc + (Number(r?.valor_total) || 0), 0);
                resSummary.textContent =
                    `Total valor procesado: $${fmt(totalValor)} · Seleccionadas: ${results.length} · Solicitadas: ${(json?.requested_ids||[]).length}`;
            }

            bulkForm?.addEventListener('submit', async (e) => {
                e.preventDefault();

                const ids = items.filter(i => i.checked).map(i => i.value);
                if (!ids.length) {
                    alert('No hay facturas seleccionadas.');
                    return;
                }

                setCardVisible(true);
                setLoading(true);

                try {
                    const resp = await fetch(bulkForm.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            ids,
                            cargar: true
                        })
                    });

                    const json = await resp.json();
                    renderSummary(json);

                    if (!resp.ok) {
                        console.error('HTTP', resp.status, json);
                        alert('Ocurrió un error al cargar a BC. Revisa el resumen.');
                    }
                } catch (err) {
                    console.error(err);
                    setCardVisible(true);
                    resProcessed.textContent = '0';
                    resOk.textContent = '0';
                    resErr.textContent = '0';
                    resMiss.textContent = '0';
                    resSummary.textContent = 'Error de red o servidor.';
                } finally {
                    setLoading(false);
                }
            });

            btnHide?.addEventListener('click', () => setCardVisible(false));
        })();
    </script>

    <script>
        (function() {
            const $ = (s, c = document) => c.querySelector(s);
            const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

            const checkAll = $('#checkAll');
            const rows = $$('#tablaFacturas .js-row');
            const items = $$('#tablaFacturas .ck-item');
            const selCount = $('#selCount');
            const bulkApply = $('#bulkApply');
            const bulkClear = $('#bulkClear');
            const bulkForm = $('#bulkForm');

            function updateUI() {
                const count = items.filter(i => i.checked).length;
                selCount.textContent = count;
                bulkApply.disabled = (count === 0);
                bulkClear.disabled = (count === 0);

                const all = items.length > 0 && count === items.length;
                checkAll.checked = all;
                checkAll.indeterminate = count > 0 && count < items.length;

                rows.forEach(r => {
                    const ck = r.querySelector('.ck-item');
                    r.classList.toggle('row-selected', ck.checked);
                });
            }

            checkAll?.addEventListener('change', () => {
                items.forEach(i => i.checked = checkAll.checked);
                updateUI();
            });

            items.forEach(i => i.addEventListener('change', updateUI));

            rows.forEach(r => {
                r.addEventListener('click', (e) => {
                    const tag = e.target.tagName.toLowerCase();
                    if (['input', 'label', 'a', 'button', 'i'].includes(tag)) return;
                    const ck = r.querySelector('.ck-item');
                    ck.checked = !ck.checked;
                    updateUI();
                });
            });

            let lastIndex = null;
            items.forEach((ck, idx) => {
                ck.addEventListener('click', (e) => {
                    if (e.shiftKey && lastIndex !== null) {
                        const [a, b] = idx > lastIndex ? [lastIndex, idx] : [idx, lastIndex];
                        const val = items[lastIndex].checked;
                        for (let j = a; j <= b; j++) items[j].checked = val;
                    }
                    lastIndex = idx;
                    updateUI();
                });
            });

            bulkClear?.addEventListener('click', () => {
                items.forEach(i => i.checked = false);
                updateUI();
            });

            bulkForm?.addEventListener('submit', (e) => {
                const action = $('#bulkAction')?.value || '';
                const count = items.filter(i => i.checked).length;
                if (count === 0) {
                    e.preventDefault();
                    alert('No hay facturas seleccionadas.');
                    return;
                }
            });

            updateUI();
        })();
    </script>
@endsection
