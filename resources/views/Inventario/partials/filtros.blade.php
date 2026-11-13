{{-- Barra de filtros reutilizable para tablas.
     @include('Inventario.partials.filtros', [
         'accion' => url-del-listado,
         'total'  => $paginador->total(),            // opcional: número de resultados
         'campos' => [
             ['tipo' => 'texto',  'nombre' => 'texto', 'placeholder' => 'Buscar…'],
             ['tipo' => 'select', 'nombre' => 'categoria', 'etiqueta' => 'Categoría', 'opciones' => [id => 'Nombre', ...]],
             ['tipo' => 'fecha',  'nombre' => 'desde', 'etiqueta' => 'Desde'],
             ['tipo' => 'orden',  'nombre' => 'orden', 'opciones' => ['clave' => 'Texto', ...]],
         ],
     ])
     Los parámetros que no son filtros (por ejemplo "page") se descartan al filtrar. --}}
@php
    $activos = [];
    foreach ($campos as $c) {
        $valor = request($c['nombre']);
        if ($valor === null || $valor === '' || $c['tipo'] === 'orden') {
            continue;
        }
        $texto = match ($c['tipo']) {
            'select' => $c['opciones'][$valor] ?? $valor,
            'fecha' => \Carbon\Carbon::parse($valor)->locale('es')->isoFormat('D MMM YYYY'),
            'numero' => ($c['prefijo'] ?? '') . number_format((float) $valor, 0, ',', '.'),
            default => '"' . $valor . '"',
        };
        $sinEste = request()->except([$c['nombre'], 'page']);
        $activos[] = [
            'etiqueta' => ($c['etiqueta'] ?? 'Búsqueda') . ': ' . $texto,
            'url' => $accion . (count($sinEste) ? '?' . http_build_query($sinEste) : ''),
        ];
    }
    $uid = 'flt' . substr(md5($accion), 0, 6);
@endphp

<form method="GET" action="{{ $accion }}" class="inv-filtrobar" role="search" id="{{ $uid }}">
    @foreach ($campos as $c)
        @switch($c['tipo'])
            @case('texto')
                <div class="inv-filtrobar__campo inv-filtrobar__campo--texto">
                    <label class="sr-only" for="{{ $uid }}_{{ $c['nombre'] }}">{{ $c['etiqueta'] ?? 'Buscar' }}</label>
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="search" id="{{ $uid }}_{{ $c['nombre'] }}" name="{{ $c['nombre'] }}" class="form-control"
                           value="{{ request($c['nombre']) }}" placeholder="{{ $c['placeholder'] ?? 'Buscar…' }}" autocomplete="off">
                </div>
                @break

            @case('select')
            @case('orden')
                <div class="inv-filtrobar__campo">
                    <label for="{{ $uid }}_{{ $c['nombre'] }}">{{ $c['etiqueta'] ?? 'Ordenar por' }}</label>
                    <select id="{{ $uid }}_{{ $c['nombre'] }}" name="{{ $c['nombre'] }}" class="custom-select js-autofiltro">
                        @if ($c['tipo'] === 'select')
                            <option value="">{{ $c['todos'] ?? 'Todos' }}</option>
                        @endif
                        @foreach ($c['opciones'] as $clave => $texto)
                            <option value="{{ $clave }}" @selected((string) request($c['nombre'], $c['tipo'] === 'orden' ? ($c['defecto'] ?? '') : '') === (string) $clave)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                @break

            @case('numero')
                <div class="inv-filtrobar__campo">
                    <label for="{{ $uid }}_{{ $c['nombre'] }}">{{ $c['etiqueta'] }}</label>
                    <input type="number" id="{{ $uid }}_{{ $c['nombre'] }}" name="{{ $c['nombre'] }}" class="form-control"
                           value="{{ request($c['nombre']) }}" placeholder="{{ $c['placeholder'] ?? '' }}" min="0" step="any">
                </div>
                @break

            @case('fecha')
                <div class="inv-filtrobar__campo">
                    <label for="{{ $uid }}_{{ $c['nombre'] }}">{{ $c['etiqueta'] }}</label>
                    <input type="date" id="{{ $uid }}_{{ $c['nombre'] }}" name="{{ $c['nombre'] }}" class="form-control js-autofiltro"
                           value="{{ request($c['nombre']) }}">
                </div>
                @break
        @endswitch
    @endforeach

    <div class="inv-filtrobar__botones">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-1"></i> Filtrar</button>
    </div>
</form>

@if (count($activos) || isset($total))
    <div class="inv-filtrobar__estado">
        @isset($total)
            <span class="inv-filtrobar__total">{{ number_format($total, 0, ',', '.') }} {{ $total === 1 ? 'resultado' : 'resultados' }}</span>
        @endisset
        @foreach ($activos as $a)
            <a href="{{ $a['url'] }}" class="inv-chip-filtro" title="Quitar este filtro">{{ $a['etiqueta'] }} <i class="fas fa-times ml-1"></i></a>
        @endforeach
        @if (count($activos) > 1 || (count($activos) && request()->filled('orden')))
            <a href="{{ $accion }}" class="inv-chip-filtro inv-chip-filtro--limpiar">Quitar todos</a>
        @endif
    </div>
@endif

@once
    @push('js')
        <script>
            // Las listas y fechas filtran solas al cambiar; la búsqueda, al presionar Enter o "Filtrar".
            $(document).on('change', '.inv-filtrobar .js-autofiltro', function () {
                var form = this.form;
                $(form).find('input, select').each(function () {
                    if (this.name && this.value === '') this.disabled = true; // URL limpia
                });
                form.submit();
            });
            $(document).on('submit', '.inv-filtrobar', function () {
                $(this).find('input, select').each(function () {
                    if (this.name && this.value === '') this.disabled = true;
                });
            });
        </script>
    @endpush
@endonce
