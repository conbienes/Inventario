@php
    $divId = optional(auth()->user())->id_div;

    // Mapa de logos por empresa (id_div)
    $logos = [
        1 => ['file' => 'logo.jpg', 'max' => '70%', 'alt' => 'Conbienes'],
        2 => ['file' => 'logoadmin.png', 'max' => '50%', 'alt' => 'Administración'],
        3 => ['file' => 'logobolera.png', 'max' => '50%', 'alt' => 'Bolera'],
        'default' => ['file' => 'logo.png', 'max' => '100%', 'alt' => 'Logo'],
    ];

    $logo = $logos[$divId] ?? $logos['default'];

    // Ruta de inicio por módulo
    $modulo = session('modulo_seleccionado');
    $homeRoutes = [
        1 => 'InicioInventario',
        2 => 'InicioInventario',
        3 => 'BonoRegalo.inicio',
    ];
    $homeRoute = $homeRoutes[$modulo] ?? 'InicioInventario';
@endphp

<a href="{{ route($homeRoute) }}" class="brand-link d-flex justify-content-center">
    <img src="{{ asset("assets/$theme/dist/img/{$logo['file']}") }}" class="img-fluid"
        style="max-width: {{ $logo['max'] }}; width:auto; height:auto;" alt="{{ $logo['alt'] }}" loading="lazy">
</a>



<nav class="mt-2">
    @php
        $uid = auth()->id();
        $puedeVender = tienePermisoModulo($uid, 3, [1, 2, 3]);
        $puedeTarjetas = tienePermisoModulo($uid, 3, [1, 2, 5]);
        $esAdmin = tienePermisoModulo($uid, 3, [1]);
    @endphp

    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
            <a href="{{ route('BonoRegalo.inicio') }}" class="nav-link {{ request()->routeIs('BonoRegalo.inicio') ? 'active' : '' }}">
                <i class="nav-icon fas fa-home"></i>
                <p>Inicio</p>
            </a>
        </li>

        @if ($puedeVender)
            <li class="nav-header">VENTAS</li>
            <li class="nav-item">
                <a href="{{ route('BonoRegalo.IndexFacturas') }}" class="nav-link {{ request()->routeIs('BonoRegalo.IndexFacturas') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-cash-register"></i>
                    <p>Nueva venta</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('BonoRegalo.informesTarjetas') }}" class="nav-link {{ request()->routeIs('BonoRegalo.informesTarjetas') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-receipt"></i>
                    <p>Recibos de caja</p>
                </a>
            </li>

            @php $enClientes = request()->routeIs('BonoRegalo.BuscarCliente', 'BonoRegalo.IndexCliente', 'BonoRegalo.editarCliente'); @endphp
            <li class="nav-item has-treeview {{ $enClientes ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $enClientes ? 'active' : '' }}">
                    <i class="nav-icon fas fa-users"></i>
                    <p>Clientes <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('BonoRegalo.BuscarCliente') }}" class="nav-link {{ request()->routeIs('BonoRegalo.BuscarCliente', 'BonoRegalo.editarCliente') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-search"></i>
                            <p>Consultar</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('BonoRegalo.IndexCliente') }}" class="nav-link {{ request()->routeIs('BonoRegalo.IndexCliente') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-plus"></i>
                            <p>Crear</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        @if ($puedeTarjetas)
            <li class="nav-header">TARJETAS</li>
            @php $enTarjetas = request()->routeIs('BonoRegalo.BuscarTarjeta', 'BonoRegalo.IndexTarjeta', 'BonoRegalo.editarTarjeta'); @endphp
            <li class="nav-item has-treeview {{ $enTarjetas ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $enTarjetas ? 'active' : '' }}">
                    <i class="nav-icon fas fa-credit-card"></i>
                    <p>Tarjetas <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('BonoRegalo.BuscarTarjeta') }}" class="nav-link {{ request()->routeIs('BonoRegalo.BuscarTarjeta', 'BonoRegalo.editarTarjeta') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-search"></i>
                            <p>Consultar</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('BonoRegalo.IndexTarjeta') }}" class="nav-link {{ request()->routeIs('BonoRegalo.IndexTarjeta') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-plus-circle"></i>
                            <p>Crear</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        <li class="nav-header">REPORTES</li>
        <li class="nav-item">
            <a href="{{ route('BonoRegalo.ReporteVentas') }}" class="nav-link {{ request()->routeIs('BonoRegalo.ReporteVentas') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-line"></i>
                <p>Reporte de ventas</p>
            </a>
        </li>
        @if ($puedeTarjetas)
            <li class="nav-item">
                <a href="{{ route('BonoRegalo.Contabilidad') }}" class="nav-link {{ request()->routeIs('BonoRegalo.Contabilidad') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                    <p>Contabilidad</p>
                </a>
            </li>
        @endif

        @if ($esAdmin)
            <li class="nav-header">ADMINISTRACIÓN</li>
            <li class="nav-item">
                <a href="{{ route('consultar_Empleado') }}" class="nav-link {{ request()->routeIs('consultar_Empleado') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-id-badge"></i>
                    <p>Empleados</p>
                </a>
            </li>
        @endif
    </ul>
</nav>
