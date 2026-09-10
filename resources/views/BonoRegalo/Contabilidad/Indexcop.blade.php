@extends("theme.$theme.layout")

@section('content')
    <div class="container-fluid px-2 px-md-3 py-3 contabilidad-wide">
        {{-- Filtros --}}
        <form method="GET" action="{{ route('BonoRegalo.Contabilidad') }}" id="filtrosForm"
            class="card mb-2 shadow-sm position-relative compact-filters">

            {{-- Overlay Cargando --}}
            <div id="fltLoading" class="flt-overlay d-none">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
            </div>

            <div class="card-body py-2 px-2">
                <div class="row g-compact align-items-end">
                    {{-- Buscar --}}
                    <div class="col-12 col-md-2 mb-2">
                        <label class="form-label form-label-compact">Buscar</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="q" value="{{ $q ?? '' }}"
                                class="form-control form-control-sm" placeholder="N° factura o nombre de cliente"
                                autocomplete="off" id="flt_q">
                        </div>
                    </div>

                    {{-- Fechas --}}
                    <div class="col-6 col-md-1 mb-2">
                        <label class="form-label form-label-compact">Desde</label>
                        <input type="date" name="desde" value="{{ $desde ?? '' }}"
                            class="form-control form-control-sm" max="{{ now()->toDateString() }}" id="flt_from">
                    </div>
                    <div class="col-6 col-md-1 mb-2">
                        <label class="form-label form-label-compact">Hasta</label>
                        <input type="date" name="hasta" value="{{ $hasta ?? '' }}"
                            class="form-control form-control-sm" max="{{ now()->toDateString() }}" id="flt_to">
                    </div>

                    {{-- Rangos rápidos --}}
                    <div class="col-12 col-md-4 mb-2">
                        <label class="form-label form-label-compact d-block">Rango rápido</label>
                        <div class="d-flex flex-wrap quick-range q-compact">
                            <button class="btn btn-outline-secondary btn-compact" type="button" data-range="hoy">
                                <i class="far fa-calendar-check mr-1"></i> Hoy
                            </button>
                            <button class="btn btn-outline-secondary btn-compact" type="button" data-range="7">
                                <i class="far fa-calendar-alt mr-1"></i> 7 días
                            </button>
                            <button class="btn btn-outline-secondary btn-compact" type="button" data-range="30">
                                <i class="far fa-calendar-alt mr-1"></i> 30 días
                            </button>
                            <button class="btn btn-outline-secondary btn-compact" type="button" data-range="mes">
                                <i class="far fa-calendar mr-1"></i> Este mes
                            </button>
                        </div>

                        <div class="col-12 d-flex  mt-1">
                            <button class="btn btn-primary btn-compact mr-2 mb-1">
                                <i class="fas fa-filter mr-1"></i> Cargar
                            </button>
                            <a href="{{ route('BonoRegalo.Contabilidad') }}"
                                class="btn btn-outline-secondary btn-compact mb-1">
                                <i class="fas fa-eraser mr-1"></i> Limpiar
                            </a>
                        </div>
                    </div>

                    {{-- Estado BC (chips) --}}
                    <div class="col-12 mb-1">
                        <label class="form-label form-label-compact d-block">Estado BC</label>

                        @php
                            $allEstados = $allEstados ?? ['ENVIADO', 'ERROR', 'PENDIENTE'];
                            $estadosSel = (array) ($estados ?? $allEstados);
                            $todosMarcados = empty(array_diff($allEstados, $estadosSel));
                        @endphp

                        <div class="estado-chips d-flex flex-wrap">
                            <label class="chip chip-all">
                                <input type="checkbox" id="chk_all" {{ $todosMarcados ? 'checked' : '' }}>
                                <span class="chip-pill chip-pill-compact"><i class="fas fa-layer-group mr-1"></i>
                                    Todos</span>
                            </label>

                            @foreach ($allEstados as $e)
                                <label class="chip" data-e="{{ $e }}">
                                    <input type="checkbox" name="estado[]" value="{{ $e }}" class="chk_estado"
                                        {{ in_array($e, $estadosSel) ? 'checked' : '' }}>
                                    <span class="chip-pill chip-pill-compact">
                                        @if ($e === 'ENVIADO')
                                            <i class="fas fa-paper-plane mr-1"></i>
                                        @elseif($e === 'ERROR')
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                        @elseif($e === 'PENDIENTE')
                                            <i class="fas fa-hourglass-half mr-1"></i>
                                        @endif
                                        {{ $e }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                    </div>
                    {{-- Acciones --}}

                </div>
            </div>
        </form>

        {{-- Resultado de carga a BC (resumen) --}}
        <div id="bcResults" class="card mt-3 shadow-sm d-none position-relative">
            <div id="bcLoading" class="flt-overlay d-none">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
            </div>

            <div class="card-header">
                <i class="fas fa-cloud-upload-alt mr-1"></i> Resultado de carga a Business Central
            </div>

            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="small text-muted">Procesadas</div>
                        <div class="h3 mb-0" id="bcProcessed">0</div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="small text-muted">Enviadas</div>
                        <div class="h3 mb-0 text-success" id="bcOk">0</div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="small text-muted">Errores</div>
                        <div class="h3 mb-0 text-danger" id="bcErr">0</div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="small text-muted">No encontradas</div>
                        <div class="h3 mb-0 text-warning" id="bcMiss">0</div>
                    </div>
                </div>

                <div class="text-center">
                    <span id="bcSummaryText" class="text-muted small d-block"></span>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <button id="bcHide" type="button" class="btn btn-sm btn-outline-secondary">Ocultar</button>
            </div>
        </div>


        {{-- Barra de acciones masivas --}}
        <form id="bulkForm" method="POST" action="{{ route('BonoRegalo.CargarBC') }}" class="card mb-2 shadow-sm">
            @csrf
            <div class="card-body py-2 d-flex flex-wrap align-items-center">
                <div class="mr-3 mb-2">
                    <span class="badge badge-pill badge-primary p-2">
                        <i class="fas fa-check-square mr-1"></i>
                        <span id="selCount">0</span> seleccionadas
                    </span>
                </div>


                <button type="submit" id="bulkApply" class="btn btn-sm btn-primary mr-2 mb-2" disabled>
                    <i class="fas fa-play mr-1"></i> Aplicar
                </button>

                <button type="button" id="bulkClear" class="btn btn-sm btn-outline-secondary mb-2" disabled>
                    <i class="fas fa-times mr-1"></i> Limpiar selección
                </button>

                <button type="button" id="btnLoadClients" class="btn btn-sm btn-outline-success mb-2">
                    <i class="fas fa-user-plus mr-1"></i> Cargar clientes (pendientes)
                </button>

            </div>



            {{-- Tabla --}}
            <div class="card mb-0">
                <div class="card-body p-0">
                    <div class="table-responsive" id="tablaWrap">

                        <table class="table table-sm table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:36px;">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    {{-- <th>Cliente</th> --}}
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Pagado</th>
                                    <th>Estado BC</th>
                                    {{-- <th>Usuario</th> --}}
                                </tr>
                            </thead>
                            <tbody id="tablaFacturas">
                                @forelse ($facturas as $f)
                                    @php
                                        $pagado = (float) ($f->pagado ?? 0);
                                        $total = (float) $f->total;
                                        $saldo = max(0, $total - $pagado);
                                        $pct = $total > 0 ? round(($pagado * 100) / $total) : 0;
                                    @endphp
                                    <tr class="js-row" data-id="{{ $f->id }}">
                                        <td class="align-middle">
                                            <input type="checkbox" name="ids[]" value="{{ $f->id }}"
                                                class="ck-item">
                                        </td>
                                        <td class="align-middle">{{ $f->numero_factura ?? $f->id }}</td>
                                        <td class="align-middle">
                                            {{ \Illuminate\Support\Carbon::parse($f->fecha)->format('Y-m-d') }}</td>
                                        {{-- <td class="align-middle">{{ $f->cliente->nombre ?? 'N/D' }}</td> --}}
                                        <td class="align-middle text-end">${{ number_format($total, 0, ',', '.') }}</td>
                                        <td class="align-middle text-end">
                                            <div>${{ number_format($pagado, 0, ',', '.') }}</div>
                                            <div class="progress" style="height:6px;">
                                                <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-info' }}"
                                                    role="progressbar" style="width: {{ $pct }}%;"
                                                    aria-valuenow="{{ $pct }}" aria-valuemin="0"
                                                    aria-valuemax="100"></div>
                                            </div>
                                        </td>

                                        <td class="align-middle">
                                            <span
                                                class="badge
                      @if ($f->estado_bc === 'PENDIENTE') bg-warning text-dark
                      @elseif($f->estado_bc === 'ENVIADO') bg-info
                      @elseif($f->estado_bc === 'ERROR') bg-danger
                      @else bg-secondary @endif">
                                                @if ($f->estado_bc === 'PENDIENTE')
                                                    <i class="fas fa-hourglass-half mr-1"></i>
                                                @elseif($f->estado_bc === 'ENVIADO')
                                                    <i class="fas fa-paper-plane mr-1"></i>
                                                @elseif($f->estado_bc === 'ERROR')
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                @endif
                                                {{ $f->estado_bc }}
                                            </span>
                                        </td>
                                        {{-- <td class="align-middle">{{ $f->usuario->nombre ?? 'N/D' }}</td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">Sin facturas para el
                                            criterio.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($facturas->hasPages())
                    <div class="card-footer">
                        {{ $facturas->links() }}
                    </div>
                @endif
            </div>
        </form>
    </div>

    {{-- Estilos --}}
    <style>
        /* ===== Compacta todo el bloque de filtros ===== */
        .compact-filters .card-body {
            padding: .5rem .6rem;
        }

        .g-compact {
            margin-left: -.25rem;
            margin-right: -.25rem;
        }

        .g-compact>[class*="col-"] {
            padding-left: .25rem;
            padding-right: .25rem;
        }

        /* Etiquetas más pequeñas y pegadas al control */
        .form-label-compact {
            font-size: .78rem;
            margin-bottom: .15rem;
            color: #374151;
        }

        /* Inputs realmente compactos */
        .input-group-sm .input-group-text {
            padding: .22rem .4rem;
            font-size: .8rem;
        }

        .form-control-sm {
            padding: .26rem .45rem;
            height: calc(1.3em + .5rem + 2px);
            font-size: .86rem;
        }

        /* Botones “micro” */
        .btn-compact {
            padding: .28rem .55rem;
            font-size: .84rem;
            line-height: 1.2;
        }

        .q-compact .btn-compact {
            margin-right: .3rem;
            margin-bottom: .3rem;
        }

        /* Chips: una fila con scroll horizontal + tamaño reducido */
        .estado-chips {
            display: block;
            white-space: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            padding-bottom: .1rem;
            /* espacio para la barra de scroll */
        }

        .estado-chips .chip {
            display: inline-block;
            margin: 0 .3rem .3rem 0;
        }

        .chip-pill-compact {
            display: inline-block;
            padding: .28rem .55rem;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            font-size: .8rem;
            background: #fff;
        }

        /* Colores (como ya tenías) */
        .estado-chips .chip[data-e="ENVIADO"] .chip-pill-compact {
            border-color: #b8e2ff;
            background: #f4fbff;
            color: #0c5460;
        }

        .estado-chips .chip[data-e="ERROR"] .chip-pill-compact {
            border-color: #ffd4d4;
            background: #fff7f7;
            color: #721c24;
        }

        .estado-chips .chip[data-e="PENDIENTE"] .chip-pill-compact {
            border-color: #ffe8a1;
            background: #fffcf3;
            color: #856404;
        }

        .estado-chips .chip[data-e="ENVIADO"] input:checked+.chip-pill-compact {
            background: #17a2b8;
            border-color: #17a2b8;
            color: #fff;
        }

        .estado-chips .chip[data-e="ERROR"] input:checked+.chip-pill-compact {
            background: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }

        .estado-chips .chip[data-e="PENDIENTE"] input:checked+.chip-pill-compact {
            background: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .chip-all input:checked+.chip-pill-compact {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        /* Ajustes finos en pantallas grandes: todo en una línea si cabe */
        @media (min-width: 1200px) {
            .compact-filters .row.align-items-end {
                align-items: center !important;
            }

            .compact-filters .mb-2 {
                margin-bottom: .4rem !important;
            }
        }

        thead.table-light th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: linear-gradient(180deg, #f8fafc, #edf2ff);
        }

        /* Opcional: compacta un poco filas y celdas para que quepan más */
        .table-sm td,
        .table-sm th {
            padding: .36rem .5rem;
        }

        .table tbody tr {
            line-height: 1.15;
        }

        /* Overlay de cargando */
        .flt-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .6);
            z-index: 5;
        }

        .flt-overlay.d-none {
            display: none !important;
        }

        /* Chips estado */
        .estado-chips .chip {
            margin: 0 .5rem .5rem 0;
            cursor: pointer;
            user-select: none;
        }

        .estado-chips .chip input {
            display: none;
        }

        .estado-chips .chip .chip-pill {
            display: inline-block;
            padding: .45rem .75rem;
            border: 1px solid #dee2e6;
            border-radius: 2rem;
            font-size: .85rem;
            line-height: 1.1;
            background: #fff;
            color: #495057;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .04);
            transition: all .2s ease;
        }

        .estado-chips .chip .chip-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .07);
        }

        /* “Todos” */
        .estado-chips .chip-all .chip-pill {
            border-style: dashed;
        }

        .estado-chips .chip-all input:checked+.chip-pill {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        /* Colores por estado (sin marcar) */
        .estado-chips .chip[data-e="ENVIADO"] .chip-pill {
            border-color: #b8e2ff;
            background: #f4fbff;
            color: #0c5460;
        }

        .estado-chips .chip[data-e="ERROR"] .chip-pill {
            border-color: #ffd4d4;
            background: #fff7f7;
            color: #721c24;
        }

        .estado-chips .chip[data-e="PENDIENTE"] .chip-pill {
            border-color: #ffe8a1;
            background: #fffcf3;
            color: #856404;
        }

        /* Colores marcados */
        .estado-chips .chip[data-e="ENVIADO"] input:checked+.chip-pill {
            background: #17a2b8;
            border-color: #17a2b8;
            color: #fff;
        }

        .estado-chips .chip[data-e="ERROR"] input:checked+.chip-pill {
            background: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }

        .estado-chips .chip[data-e="PENDIENTE"] input:checked+.chip-pill {
            background: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        /* Botones rango rápido (BS4 no tiene gap) */
        .quick-range .btn {
            margin-right: .5rem;
            margin-bottom: .5rem;
        }

        /* Fila seleccionada */
        .js-row.row-selected {
            background: #eef5ff !important;
            box-shadow: inset 3px 0 0 #0d6efd;
        }

        /* Cursor para selección por fila */
        #tablaFacturas tr.js-row {
            cursor: pointer;
        }

        #tablaFacturas tr.js-row td:first-child,
        #tablaFacturas tr.js-row td:first-child * {
            cursor: default;
        }

        /* ========= TABLA ========= */
        .table-responsive {
            max-height: 68vh;
            /* scroll elegante con header fijo */
            overflow: auto;
            border-radius: 14px;
            box-shadow: 0 10px 28px rgba(2, 6, 23, .06);
        }

        table.table {
            margin: 0;
            background: #fff;
        }

        thead.table-light th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: linear-gradient(180deg, #f8fafc, #edf2ff);
            color: #0f172a;
            font-weight: 700;
            letter-spacing: .2px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table tbody tr {
            transition: background .15s, box-shadow .15s;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .js-row.row-selected {
            background: #eef2ff !important;
            box-shadow: inset 3px 0 0 var(--ui-primary);
        }

        .table td,
        .table th {
            vertical-align: middle;
        }
    </style>
    <script>
        (function() {
            const wrap = document.getElementById('tablaWrap');
            if (!wrap) return;

            const footer = document.querySelector('.main-footer'); // si usas AdminLTE
            const GAP = 12; // respiración inferior

            function ajustarAlto() {
                // top del contenedor respecto al viewport
                const rect = wrap.getBoundingClientRect();
                const hFooter = footer ? footer.offsetHeight : 0;

                // altura disponible: alto ventana - lo que ya “gastamos” arriba - footer - gap
                const disponible = Math.max(260, window.innerHeight - rect.top - hFooter - GAP);

                wrap.style.maxHeight = disponible + 'px';
                wrap.style.overflow = 'auto';
            }

            // recalcular al cargar y al redimensionar
            window.addEventListener('resize', ajustarAlto);
            document.addEventListener('DOMContentLoaded', ajustarAlto);
            // micro-tick por si el layout cambia tras render:
            setTimeout(ajustarAlto, 0);
        })();
    </script>


    <script>
        (function() {
            const $ = (s, c = document) => c.querySelector(s);

            const btnLoad = $('#btnLoadClients');

            // Card resumen (ya existen en tu vista)
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
                        }) // opcional: limita cuántos cargar por lote
                    });

                    const json = await resp.json();

                    // Mapea el resumen a tu card:
                    const processed = Number(json?.processed || 0);
                    const ok = Number(json?.ok || 0);
                    const errors = Number(json?.errors || 0);

                    resProcessed.textContent = processed;
                    resOk.textContent = ok;
                    resErr.textContent = errors;
                    resMiss.textContent = 0; // no aplica para clientes

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

    {{-- JS: filtros (auto), chips reactivos, doble click exclusivo, rangos rápidos, debounce y clamp fechas --}}
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
            const chipLabs = $$('.estado-chips .chip[data-e]');
            const rangeBtns = $$('.quick-range [data-range]');

            function showLoading(v) {
                if (!loading) return;
                loading.classList.toggle('d-none', !v);
            }

            function autoSubmit() {
                showLoading(true);
                form.submit();
            }

            // Clamp fechas: desde <= hasta
            function clampDates() {
                if (fromI.value && toI.value && fromI.value > toI.value) toI.value = fromI.value;
            }

            // Debounce buscar
            let t = null;
            qInput?.addEventListener('input', () => {
                clearTimeout(t);
                t = setTimeout(autoSubmit, 500);
            });

            // Fechas auto
            [fromI, toI].forEach(el => el?.addEventListener('change', () => {
                clampDates();
                autoSubmit();
            }));

            // Rango rápido
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

            // Select all
            chkAll?.addEventListener('change', () => {
                chks.forEach(c => c.checked = chkAll.checked);
                autoSubmit();
            });

            // Cualquier estado -> auto
            chks.forEach(c => c.addEventListener('change', () => {
                const on = chks.filter(i => i.checked).length;
                chkAll.checked = (on === chks.length);
                autoSubmit();
            }));

            // Doble click en chip = solo ese estado
            chipLabs.forEach(lab => {
                lab.addEventListener('dblclick', () => {
                    const val = lab.getAttribute('data-e');
                    chks.forEach(c => c.checked = (c.value === val));
                    chkAll.checked = false;
                    autoSubmit();
                });
            });

            // Seguridad: si no hay ninguno marcado, marcar todos al enviar
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

            // Card resumen
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

            // Intercepta submit para POST por fetch y pinta solo resumen
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


    {{-- JS: selección (click en fila, shift+click, seleccionar todo, contador) --}}
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

            // Click en fila (excepto controles)
            rows.forEach(r => {
                r.addEventListener('click', (e) => {
                    const tag = e.target.tagName.toLowerCase();
                    if (['input', 'label', 'a', 'button', 'i'].includes(tag)) return;
                    const ck = r.querySelector('.ck-item');
                    ck.checked = !ck.checked;
                    updateUI();
                });
            });

            // Shift + click rango
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

            // Validar acción
            bulkForm?.addEventListener('submit', (e) => {
                const action = $('#bulkAction')?.value || '';
                const count = items.filter(i => i.checked).length;
                if (count === 0) {
                    e.preventDefault();
                    alert('No hay facturas seleccionadas.');
                    return;
                }
            });

            // Inicial
            updateUI();
        })();
    </script>
@endsection
