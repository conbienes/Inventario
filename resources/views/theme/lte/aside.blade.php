<aside class="main-sidebar elevation-4" style="background-color:#005484">
    @php
        $user = Auth::user();

        // 1) Trae la relación sólo si no está cargada
        $user->loadMissing(['modulos:id']); // <- 1 query

        // 2) IDs de módulos habilitados para este usuario
        $idsModulos = $user->modulos->pluck('id')->all();

        // 3) Módulo actual de la sesión
        $moduloSeleccionado = (int) (session('modulo_seleccionado') ?? 0);

        // 4) Mapa módulo -> componente Blade
        $sidebars = [
            1 => 'sidebar-inventario',
            3 => 'sidebar-bono-regalo',
            // agrega más: 2 => 'sidebar-otro', ...
        ];

        $component = $sidebars[$moduloSeleccionado] ?? null;

        // 5) Autorización simple: debe tener ese módulo
        $autorizado = $component && in_array($moduloSeleccionado, $idsModulos, true);
    @endphp

    <div class="sidebar">
        @if ($autorizado)
            {{-- Render dinámico del componente --}}
            <x-dynamic-component :component="$component" />
        @endif
    </div>
</aside>
