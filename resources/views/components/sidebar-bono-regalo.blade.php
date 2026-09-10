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

<a href="{{ route($homeRoute) }}" class="brand-link">
    <div class="row justify-content-center">
        <img src="{{ asset("assets/$theme/dist/img/{$logo['file']}") }}" class="center img-fluid"
            style="max-width: {{ $logo['max'] }}; width:auto; height:auto;" alt="{{ $logo['alt'] }}" loading="lazy">
    </div>
</a>



<nav class="mt-1">

    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        @if (tienePermisoModulo(auth()->id(), 3, [1, 2, 3]))
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link active">
                    <i class="far fa-address-book"></i>
                    <p>Clientes <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('BonoRegalo.BuscarCliente') }}" class="nav-link">
                            <i class="fas fa-search" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Consultar</p>
                        </a>
                    </li>
                </ul>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('BonoRegalo.IndexCliente') }}" class="nav-link">
                            <i class="fas fa-plus-circle" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Crear</p>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item has-treeview">
                <a href="#" class="nav-link active">
                    <i class="far fa-address-book"></i>
                    <p>Ventas Bono Regalo <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('BonoRegalo.IndexFacturas') }}" class="nav-link">
                            <i class="fas fa-plus-circle" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Crear</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @if (tienePermisoModulo(auth()->id(), 3, [1, 2, 5]))
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link active">
                    <i class="fas fa-credit-card"></i>
                    <p>Tarjetas Bono Regalo <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('BonoRegalo.BuscarTarjeta') }}" class="nav-link">
                            <i class="fas fa-search" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Consultar</p>
                        </a>
                    </li>
                </ul>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('BonoRegalo.IndexTarjeta') }}" class="nav-link">
                            <i class="fas fa-plus-circle" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Crear</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @if (tienePermisoModulo(auth()->id(), 3, [1]))
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link active">
                    <i class="far fa-address-book"></i>
                    <p>Empleados <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('consultar_Empleado') }}" class="nav-link">
                            <i class="fas fa-search" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Consultar</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

    </ul>
</nav>
