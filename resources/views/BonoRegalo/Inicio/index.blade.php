@extends("theme.$theme.layout")

@push('css')
    @include('BonoRegalo.partials._estilos')
@endpush

@section('content')
    @php
        $uid = auth()->id();
        $puedeVender = tienePermisoModulo($uid, 3, [1, 2, 3]);
        $puedeTarjetas = tienePermisoModulo($uid, 3, [1, 2, 5]);
        $nombre = trim(explode(' ', (string) optional(auth()->user())->nombre)[0] ?? '');
        $hora = (int) now()->format('G');
        $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    @endphp

    <div class="container-fluid py-4">

        @foreach (['success' => 'success', 'success1' => 'warning'] as $key => $alertType)
            @if (session($key))
                <div class="alert alert-{{ $alertType }} alert-dismissible fade show shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle mr-2 fs-5"></i>
                        <span class="flex-grow-1">{{ session($key) }}</span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Bienvenida --}}
        <section class="br-hero mb-4">
            <div class="br-hero__texto">
                <span class="br-hero__etiqueta"><i class="fas fa-gift mr-1"></i> Bono Regalo</span>
                <h1>{{ $saludo }}{{ $nombre ? ', ' . $nombre : '' }}</h1>
                <p>Vende tarjetas de regalo, consulta clientes y revisa tus ventas desde aquí.</p>
                <div class="br-hero__acciones">
                    @if ($puedeVender)
                        <a href="{{ route('BonoRegalo.IndexFacturas') }}" class="btn btn-light btn-lg">
                            <i class="fas fa-cash-register mr-2"></i>Nueva venta
                        </a>
                        <a href="{{ route('BonoRegalo.BuscarCliente') }}" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-search mr-2"></i>Buscar cliente
                        </a>
                    @else
                        <a href="{{ route('BonoRegalo.ReporteVentas') }}" class="btn btn-light btn-lg">
                            <i class="fas fa-chart-line mr-2"></i>Ver reporte de ventas
                        </a>
                    @endif
                </div>
            </div>
            <div class="br-hero__figura" aria-hidden="true">
                <img src="{{ asset('assets/conbienes/img/login-bono.svg') }}" alt="">
            </div>
        </section>

        {{-- Ventas y clientes --}}
        <section class="mb-4">
            <h2 class="br-seccion">Ventas y clientes</h2>
            <div class="row">
                @if ($puedeVender)
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <a href="{{ route('BonoRegalo.IndexFacturas') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-azul"><i class="fas fa-cash-register"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Nueva venta</strong>
                                <small>Vender bonos y generar recibo</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <a href="{{ route('BonoRegalo.BuscarCliente') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-verde"><i class="fas fa-users"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Clientes</strong>
                                <small>Buscar, crear y editar</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <a href="{{ route('BonoRegalo.informesTarjetas') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-ambar"><i class="fas fa-receipt"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Recibos de caja</strong>
                                <small>Consultar y reimprimir ventas</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                @endif
                <div class="col-12 col-sm-6 col-xl-3 mb-3">
                    <a href="#" class="br-modulo" id="btnResumenRapido">
                        <span class="br-modulo__ico br-ico-cian"><i class="fas fa-chart-pie"></i></span>
                        <span class="br-modulo__txt">
                            <strong>Resumen rápido</strong>
                            <small>Total vendido por rango de fechas</small>
                        </span>
                        <i class="fas fa-chevron-right br-modulo__flecha"></i>
                    </a>
                </div>
            </div>
        </section>

        {{-- Tarjetas y reportes --}}
        <section class="mb-4">
            <h2 class="br-seccion">Tarjetas y reportes</h2>
            <div class="row">
                @if ($puedeTarjetas)
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <a href="{{ route('BonoRegalo.BuscarTarjeta') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-azul"><i class="fas fa-credit-card"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Inventario de tarjetas</strong>
                                <small>Existencias, estados e importación</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <a href="{{ route('BonoRegalo.IndexTarjeta') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-verde"><i class="fas fa-plus"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Registrar tarjeta</strong>
                                <small>Agregar tarjetas nuevas</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                @endif
                <div class="col-12 col-sm-6 col-xl-3 mb-3">
                    <a href="{{ route('BonoRegalo.ReporteVentas') }}" class="br-modulo">
                        <span class="br-modulo__ico br-ico-violeta"><i class="fas fa-chart-line"></i></span>
                        <span class="br-modulo__txt">
                            <strong>Reporte de ventas</strong>
                            <small>Gráficas y métricas</small>
                        </span>
                        <i class="fas fa-chevron-right br-modulo__flecha"></i>
                    </a>
                </div>
                @if ($puedeTarjetas)
                    <div class="col-12 col-sm-6 col-xl-3 mb-3">
                        <a href="{{ route('BonoRegalo.Contabilidad') }}" class="br-modulo">
                            <span class="br-modulo__ico br-ico-ambar"><i class="fas fa-file-invoice-dollar"></i></span>
                            <span class="br-modulo__txt">
                                <strong>Contabilidad</strong>
                                <small>Enviar información a BC</small>
                            </span>
                            <i class="fas fa-chevron-right br-modulo__flecha"></i>
                        </a>
                    </div>
                @endif
            </div>
        </section>

        {{-- Control Sidebar mejorado --}}
        <aside id="ventasSidebar" class="control-sidebar control-sidebar-dark">
            <div class="control-sidebar-content p-3" id="ventasPanel" data-today="{{ now()->toDateString() }}"
                data-monthstart="{{ now()->startOfMonth()->toDateString() }}">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold mb-0 text-white">Resumen de Ventas</h5>
                    <button type="button" class="close text-white" id="btnCerrarPanel" aria-label="Cerrar panel"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white-50">Rango de fechas</label>
                    <div class="row">
                        <div class="col-6">
                            <input type="date" id="res_from" class="form-control form-control-dark"
                                max="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-6">
                            <input type="date" id="res_to" class="form-control form-control-dark"
                                max="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2 quick-btns">
                        <button class="btn btn-sm btn-outline-light" type="button" data-range="hoy">Hoy</button>
                        <button class="btn btn-sm btn-outline-light" type="button" data-range="7">7 días</button>
                        <button class="btn btn-sm btn-outline-light" type="button" data-range="30">30 días</button>
                        <button class="btn btn-sm btn-outline-light" type="button" data-range="mes">Este mes</button>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-3">
                    <button id="res_aplicar" class="btn btn-primary flex-fill">
                        <i class="fas fa-sync-alt mr-1"></i> Actualizar
                    </button>

                    @if (Route::has('BonoRegalo.exportarFacturas'))
                        <a id="res_export" href="#" class="btn btn-success flex-fill" target="_blank"
                            rel="noopener">
                            <i class="fas fa-file-excel mr-1"></i> Exportar
                        </a>
                    @endif

                    {{-- NUEVO: Imprimir térmico --}}
                    <button type="button" id="res_print" class="btn btn-danger flex-fill"
                        data-url-base="{{ route('BonoRegalo.printResumen') }}" {{-- <-- tu ruta GET que imprime por rango --}}>
                        <i class="fas fa-print mr-1"></i> Imprimir
                    </button>

                </div>


                <div id="resumenCard" class="card bg-dark border-secondary shadow-sm mb-3">
                    <div class="card-body">
                        <div class="small text-white-50">Total vendido</div>
                        <div id="res_total" class="display-6 font-weight-bold mb-1 text-white">$0</div>
                        <div class="text-white-50" id="res_rango">—</div>
                        <div class="mt-2">
                            <span class="badge bg-secondary">
                                <i class="fas fa-receipt mr-1"></i>
                                <span id="res_tx">0</span> transacciones
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Métodos de pago --}}
                <div id="res_methods_wrap" class="mb-3" style="display:none;">
                    <h6 class="font-weight-bold mb-2 text-white">Por método de pago</h6>
                    <div id="res_methods"></div>
                </div>
            </div>
        </aside>
    </div>

    {{-- Botones flotantes mejorados --}}
    <div class="fab-container">
        <button id="btnVentasSidebar" class="btn btn-primary fab-toggle shadow" type="button"
            title="Abrir panel de ventas">
            <i class="fas fa-chart-bar"></i>
        </button>

    </div>

    @push('css')
    {{-- Estilos mejorados --}}
    <style>
        :root {
            --primary-color: #005484;
            --success-color: #15803D;
            --info-color: #0E7490;
            --warning-color: #FFC107;
            --danger-color: #B91C1C;
            --transition: all 0.3s ease;
        }

        .card-module {
            transition: var(--transition);
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-module:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1) !important;
            border-color: var(--primary-color);
        }

        .module-icon {
            transition: var(--transition);
        }

        .card-module:hover .module-icon {
            transform: scale(1.1);
        }

        .stat-card {
            transition: var(--transition);
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .icon-wrapper {
            transition: var(--transition);
        }

        .stat-card:hover .icon-wrapper {
            transform: scale(1.1);
        }

        .bg-white-20 {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .bg-dark-20 {
            background-color: rgba(0, 0, 0, 0.1);
        }

        /* Panel lateral mejorado */
        .control-sidebar-dark {
            background: #1a1a2e;
            color: #fff;
        }

        .control-sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: 400px;
            max-width: 100%;
            height: 100vh;
            z-index: 1060;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
        }

        .control-sidebar-open .control-sidebar {
            transform: translateX(0);
        }

        .control-sidebar .control-sidebar-content {
            height: 100vh;
            overflow-y: auto;
            padding-bottom: 1rem;
        }

        .form-control-dark {
            background-color: #2d3047;
            border-color: #3a3f5c;
            color: #fff;
        }

        .form-control-dark:focus {
            background-color: #2d3047;
            border-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(0, 84, 132, 0.25);
        }

        @media (max-width: 576px) {
            .control-sidebar {
                width: 100%;
            }
        }

        /* Contenedor de botones flotantes */
        .fab-container {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .fab-toggle,
        .fab-save,
        .fab-scroll {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: var(--transition);
            border: none;
        }

        .fab-toggle:hover,
        .fab-save:hover,
        .fab-scroll:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        /* Métodos de pago */
        .method-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #3a3f5c;
        }

        .method-item:last-child {
            border-bottom: none;
        }

        /* Animaciones */
        .animate__animated {
            animation-duration: 0.5s;
        }

        /* Quick buttons activos */
        .quick-btns .btn.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }

        .quick-btns .btn {
            margin: 0 .5rem .5rem 0;
            /* derecha y abajo */
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .display-6 {
                font-size: 1.75rem;
            }

            .stat-card .icon-wrapper {
                padding: 0.5rem !important;
            }

            .stat-card .icon-wrapper i {
                font-size: 1.25rem !important;
            }
        }

        /* Scroll personalizado */
        .control-sidebar-content::-webkit-scrollbar {
            width: 6px;
        }

        .control-sidebar-content::-webkit-scrollbar-track {
            background: #2d3047;
        }

        .control-sidebar-content::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }

        .control-sidebar-content::-webkit-scrollbar-thumb:hover {
            background: var(--br-primary-dark);
        }

        /* Overlay para cerrar panel en móviles */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1055;
            display: none;
        }

        @media (max-width: 576px) {
            .sidebar-overlay {
                display: block;
            }
        }
    </style>
    @endpush
    @push('js')
    <script>
        (function() {
            const $ = (s, c = document) => c.querySelector(s);

            const panel = $('#ventasPanel');
            const fromI = $('#res_from');
            const toI = $('#res_to');
            const btnPrn = $('#res_print');

            // Defaults por si están vacíos (ya los tienes en data-*)
            const fallbackFrom = panel?.dataset.monthstart || '';
            const fallbackTo = panel?.dataset.today || '';

            function getRange() {
                const from = (fromI?.value || '').trim() || fallbackFrom;
                const to = (toI?.value || '').trim() || fallbackTo;
                return {
                    from,
                    to
                };
            }

            btnPrn?.addEventListener('click', (e) => {
                e.preventDefault();
                const base = btnPrn.dataset.urlBase || '#';
                const {
                    from,
                    to
                } = getRange();
                if (!from || !to) {
                    alert('Define un rango de fechas.');
                    return;
                }

                const qs = new URLSearchParams({
                    from,
                    to
                }).toString();
                const url = `${base}?${qs}`;

                // Abre la impresión (térmica) en nueva pestaña
                window.open(url, '_blank', 'noopener');
            });
        })();
    </script>


    {{-- Script mejorado y compatible con tu backend --}}
    <script>
        (function() {
            // Utilidades
            const $ = (s, c = document) => c.querySelector(s);
            const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

            // Elementos del DOM
            const panel = $('#ventasPanel');
            const TODAY = panel?.dataset.today || new Date().toISOString().slice(0, 10);
            const MONTH_START = panel?.dataset.monthstart || new Date(new Date().getFullYear(), new Date().getMonth(),
                1).toISOString().slice(0, 10);

            // Elementos del resumen
            const elFrom = $('#res_from');
            const elTo = $('#res_to');
            const elTotal = $('#res_total');
            const elTx = $('#res_tx');
            const elRango = $('#res_rango');
            const elExport = $('#res_export');
            const quickBtns = $$('.quick-btns [data-range]');
            const btnAplicar = $('#res_aplicar');

            // Botones flotantes
            const btnToggle = $('#btnVentasSidebar');
            const btnSave = $('#btnGuardarVentas');
            const btnScroll = $('#btnScrollTop');
            const btnResumenRapido = $('#btnResumenRapido');
            const btnAyuda = $('#btnAyuda');
            const btnCerrarPanel = $('#btnCerrarPanel');

            // Rutas
            const routeResumen = `{{ route('BonoRegalo.ResumenVentas') }}`;
            @if (Route::has('BonoRegalo.exportarFacturas'))
                const routeExport = `{{ route('BonoRegalo.exportarFacturas') }}`;
            @else
                const routeExport = '';
            @endif

            // Constantes
            const STORAGE_KEY = 'br_resumen_rango';
            const fmtMoney = n => new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(n);

            const iso = d => d.toISOString().slice(0, 10);

            // Funciones de utilidad
            function setActive(tipo) {
                quickBtns.forEach(b => b.classList.toggle('active', b.dataset.range === tipo));
            }

            function clamp() {
                if (elFrom.value && elTo.value && elFrom.value > elTo.value)
                    elTo.value = elFrom.value;
            }

            function buildExportHref() {
                if (!routeExport) return;
                const params = new URLSearchParams({
                    from: elFrom.value || '',
                    to: elTo.value || ''
                });
                if (elExport) elExport.href = `${routeExport}?${params.toString()}`;
                if (btnSave) btnSave.href = `${routeExport}?${params.toString()}`;
            }

            function setRange(tipo) {
                const endStr = TODAY;
                let start = new Date(endStr + 'T00:00:00');

                if (tipo === '7') start.setDate(start.getDate() - 6);
                if (tipo === '30') start.setDate(start.getDate() - 29);
                if (tipo === 'mes') start = new Date(MONTH_START + 'T00:00:00');

                elFrom.value = iso(start);
                elTo.value = endStr;
                setActive(tipo);
                localStorage.setItem(STORAGE_KEY, JSON.stringify({
                    tipo,
                    from: elFrom.value,
                    to: elTo.value
                }));
                buildExportHref();
            }

            function mostrarCargando(mostrar = true) {
                if (btnAplicar) {
                    if (mostrar) {
                        btnAplicar.disabled = true;
                        btnAplicar.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Cargando...';
                    } else {
                        btnAplicar.disabled = false;
                        btnAplicar.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Actualizar';
                    }
                }
            }

            // Función principal para cargar el resumen - COMPATIBLE CON TU BACKEND
            async function cargarResumen() {
                clamp();
                const params = new URLSearchParams({
                    from: elFrom.value || '',
                    to: elTo.value || ''
                });
                buildExportHref();

                try {
                    mostrarCargando(true);
                    const r = await fetch(`${routeResumen}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    const j = await r.json();

                    // Actualizar datos principales - compatibles con tu estructura
                    elTotal.textContent = fmtMoney(j.total ?? 0);
                    elTx.textContent = (j.transacciones ?? 0);
                    elRango.textContent = `${j.from ?? elFrom.value} → ${j.to ?? elTo.value}`;

                    // Actualizar métodos de pago - compatibles con tu estructura
                    const wrap = $('#res_methods_wrap');
                    const cont = $('#res_methods');
                    if (wrap && cont) {
                        if (j.methods && Array.isArray(j.methods) && j.methods.length) {
                            wrap.style.display = 'block';
                            cont.innerHTML = j.methods.map(m => `
                        <div class="method-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-white-50">${m.name}</span>
                                <small class="d-block text-white-25">${m.n} transacciones</small>
                            </div>
                            <span class="font-weight-bold text-white">${fmtMoney(m.total || 0)}</span>
                        </div>`).join('');
                        } else {
                            wrap.style.display = 'none';
                            cont.innerHTML = '';
                        }
                    }

                } catch (e) {
                    console.error('Error cargando resumen:', e);
                    elTotal.textContent = fmtMoney(0);
                    elTx.textContent = '0';
                    elRango.textContent = 'Error al cargar';
                    const wrap = $('#res_methods_wrap');
                    if (wrap) wrap.style.display = 'none';

                    // Mostrar error temporal
                    const originalText = elRango.textContent;
                    elRango.textContent = 'Error al cargar datos';
                    setTimeout(() => {
                        elRango.textContent = originalText;
                    }, 3000);
                } finally {
                    mostrarCargando(false);
                }
            }

            // Gestión del panel lateral
            function abrirPanel() {
                document.body.classList.add('control-sidebar-open');
                if (btnSave) btnSave.style.display = 'flex';

                // Crear overlay para móviles
                if (window.innerWidth <= 576 && !$('#sidebarOverlay')) {
                    const overlay = document.createElement('div');
                    overlay.id = 'sidebarOverlay';
                    overlay.className = 'sidebar-overlay';
                    overlay.addEventListener('click', cerrarPanel);
                    document.body.appendChild(overlay);
                }

                // Inicializar panel si es la primera vez
                if (!panel.dataset.inicializado) {
                    const saved = localStorage.getItem(STORAGE_KEY);
                    if (saved) {
                        try {
                            const {
                                tipo,
                                from,
                                to
                            } = JSON.parse(saved);
                            if (from && to) {
                                elFrom.value = from;
                                elTo.value = to;
                                setActive(tipo || '');
                            } else {
                                setRange('hoy');
                            }
                        } catch {
                            setRange('hoy');
                        }
                    } else {
                        setRange('hoy');
                    }
                    cargarResumen();
                    panel.dataset.inicializado = 'true';
                }
            }

            function cerrarPanel() {
                document.body.classList.remove('control-sidebar-open');
                if (btnSave) btnSave.style.display = 'none';

                const overlay = $('#sidebarOverlay');
                if (overlay) overlay.remove();
            }

            // Inicialización
            function init() {
                // Eventos de fechas / atajos / actualizar
                elFrom.addEventListener('change', () => {
                    clamp();
                    buildExportHref();
                });
                elTo.addEventListener('change', () => {
                    clamp();
                    buildExportHref();
                });
                quickBtns.forEach(btn => btn.addEventListener('click', () => {
                    setRange(btn.dataset.range);
                    cargarResumen();
                }));

                if (btnAplicar) btnAplicar.addEventListener('click', cargarResumen);

                // Botón de resumen rápido
                if (btnResumenRapido) {
                    btnResumenRapido.addEventListener('click', (e) => {
                        e.preventDefault();
                        abrirPanel();
                    });
                }

                // Botón de ayuda
                if (btnAyuda) {
                    btnAyuda.addEventListener('click', () => {
                        if (window.jQuery && jQuery('#modalAyuda').length) {
                            jQuery('#modalAyuda').modal('show');
                        }
                    });
                }

                // Botón de scroll to top
                if (btnScroll) {
                    btnScroll.addEventListener('click', () => {
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    });

                    // Mostrar/ocultar botón según scroll
                    window.addEventListener('scroll', () => {
                        btnScroll.style.display = window.scrollY > 300 ? 'flex' : 'none';
                    });
                }

                // Botones del panel
                if (btnToggle) {
                    btnToggle.addEventListener('click', abrirPanel);
                }

                if (btnCerrarPanel) {
                    btnCerrarPanel.addEventListener('click', cerrarPanel);
                }

                // Cerrar con ESC
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && document.body.classList.contains('control-sidebar-open')) {
                        cerrarPanel();
                    }
                });

                // Tooltips de Bootstrap. Dentro de este script "$" es querySelector, por eso se usa jQuery explícito.
                if (window.jQuery && jQuery.fn.tooltip) {
                    jQuery('[title]').tooltip();
                }
            }

            // Inicializar cuando el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }

        })();
    </script>
    @endpush
@endsection
