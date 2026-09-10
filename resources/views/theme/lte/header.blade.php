<header class="main-header">
    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="d-flex align-items-center">
            {{-- Mostrar en qué módulo estoy --}}
            @php
                $modulos = [
                    1 => 'Inventario',
                    2 => 'Restaurante',
                    3 => 'Bono Regalo',
                ];
                $moduloActual = session('modulo_seleccionado');
            @endphp
            @if ($moduloActual && isset($modulos[$moduloActual]))
                <span class="badge badge-info mx-3">
                    <i class="fas fa-cube"></i> {{ $modulos[$moduloActual] }}
                </span>
            @endif

            <div class="btn-group dropleft">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    {{ Auth()->user()->nombre . ' ' . Auth()->user()->apellidos }}
                </button>

                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('contrasena') }}">Cambiar Contraseña</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('CambiarEmpresa') }}">Cambiar Empresa</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('Cerrar') }}">Salir</a>
                </div>
            </div>
        </div>
    </nav>
</header>
