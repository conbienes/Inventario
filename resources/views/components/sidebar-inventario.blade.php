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

        <!-- Sección de Solicitud -->
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link active">
                <i class="far fa-paper-plane"></i>
                <p>Solicitud <i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('Crear_SolicProduc') }}" class="nav-link">
                        <i class="fas fa-shopping-basket" style="color:#ffffff"></i>
                        <p style="color:#ffffff">Producto</p>
                    </a>
                </li>
            </ul>
        </li>

        @if (tienePermisoModulo(auth()->id(), 1, [1, 2]))
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link active">
                    <i class="fas fa-dolly-flatbed"></i>
                    <p>Entradas Almacen <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('EntAlmacen') }}" class="nav-link">
                            <i class="fas fa-warehouse" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Entradas Almacen</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('In_Arqueo') }}" class="nav-link">
                            <i class="fab fa-searchengin" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Arqueo</p>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Sección de Empleados (solo admin) -->
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

            <!-- Sección de Informes (solo admin) -->
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link active">
                    <i class="far fa-clipboard"></i>
                    <p>Descargar Informes <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('Exportar_Solicitudes') }}" class="nav-link">
                            <i class="fas fa-search" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Solicitudes Recibidas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('Exportar_EntradasAlmacen') }}" class="nav-link">
                            <i class="fab fa-affiliatetheme" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Entradas Almacen</p>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Sección de Configuraciones (solo admin) -->
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link active">
                    <i class="fas fa-cogs"></i>
                    <p>Configuraciones <i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('Almacen') }}" class="nav-link">
                            <i class="fas fa-warehouse" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Almacen</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('Categoria') }}" class="nav-link">
                            <i class="fas fa-cubes" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Categoria</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('Producto') }}" class="nav-link">
                            <i class="fab fa-product-hunt" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Productos</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('UnidMedida') }}" class="nav-link">
                            <i class="fas fa-balance-scale-right" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Unidades de Medida</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('Proveedor') }}" class="nav-link">
                            <i class="fas fa-parachute-box" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Proveedores</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('Inventario') }}" class="nav-link">
                            <i class="fas fa-dolly-flatbed" style="color:#ffffff"></i>
                            <p style="color:#ffffff">Inventario</p>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

    </ul>
</nav>
