@php
    $modulos = [
        1 => ['nombre' => 'Inventario', 'icono' => 'fa-boxes'],
        2 => ['nombre' => 'Restaurante', 'icono' => 'fa-utensils'],
        3 => ['nombre' => 'Bono Regalo', 'icono' => 'fa-gift'],
    ];
    $moduloActual = session('modulo_seleccionado');
    $usuario = Auth()->user();
    $nombreCompleto = trim($usuario->nombre . ' ' . $usuario->apellidos);
    $iniciales = mb_strtoupper(mb_substr((string) $usuario->nombre, 0, 1) . mb_substr((string) $usuario->apellidos, 0, 1));
@endphp

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    {{-- Botón para abrir / cerrar el menú lateral --}}
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <a class="nav-link cb-menu-btn" data-widget="pushmenu" href="#" role="button" aria-label="Mostrar u ocultar menú">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        @if ($moduloActual && isset($modulos[$moduloActual]))
            <li class="nav-item d-none d-sm-flex align-items-center ml-2">
                <span class="cb-modulo">
                    <i class="fas {{ $modulos[$moduloActual]['icono'] }}"></i> {{ $modulos[$moduloActual]['nombre'] }}
                </span>
            </li>
        @endif
    </ul>

    {{-- Usuario --}}
    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item dropdown">
            <a class="nav-link cb-usuario" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <span class="cb-avatar" aria-hidden="true">{{ $iniciales }}</span>
                <span class="d-none d-md-inline cb-usuario__nombre">{{ $nombreCompleto }}</span>
                <i class="fas fa-chevron-down cb-usuario__flecha"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right cb-dropdown">
                <div class="dropdown-header">
                    <strong class="d-block">{{ $nombreCompleto }}</strong>
                    @if ($moduloActual && isset($modulos[$moduloActual]))
                        <small>Módulo {{ $modulos[$moduloActual]['nombre'] }}</small>
                    @endif
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('contrasena') }}"><i class="fas fa-key"></i> Cambiar contraseña</a>
                <a class="dropdown-item" href="{{ route('CambiarEmpresa') }}"><i class="fas fa-building"></i> Cambiar empresa</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="{{ route('Cerrar') }}"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </li>
    </ul>
</nav>
