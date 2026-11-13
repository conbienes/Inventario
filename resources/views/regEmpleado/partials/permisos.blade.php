{{--
    Matriz de permisos por módulo + permisos especiales.
    Variables:
      $modulos, $permisos            (colecciones Modulo / Permiso)
      $permisosSeleccionados         [id_modulo => [id_permiso, ...]]  (nombre de campo: modulos[ID_MODULO][])
      $especialesSeleccionados       [codigo, ...]                     (nombre de campo: permisos_especiales[])
      $catalogoEspeciales            [codigo => descripción]           (PermisoEspecial::CODIGOS)
    Incluir una sola vez por página (el JS se registra con @push('js')).
--}}
@php
    $seleccion = collect($permisosSeleccionados ?? [])->filter(fn ($ids) => is_array($ids));
    $totalAsignados = $seleccion->sum(fn ($ids) => count($ids));
    $especiales = (array) ($especialesSeleccionados ?? []);
@endphp

<h5 class="emp-seccion">Permisos del sistema</h5>
<p class="emp-ayuda mb-2">
    Cada <strong>columna</strong> es un módulo y cada <strong>fila</strong> un permiso. Marque la cabecera de una columna
    para asignar todo el módulo, o la de una fila para asignar ese permiso en todos los módulos.
</p>

<div class="table-responsive emp-matriz-wrap">
    <table class="table table-sm table-bordered emp-matriz" id="matrizPermisos">
        <thead>
            <tr>
                <th scope="col" class="emp-celda-nombre">Permiso</th>
                @foreach ($modulos as $modulo)
                    <th scope="col">
                        <div class="custom-control custom-checkbox d-inline-block">
                            <input type="checkbox" class="custom-control-input js-columna" id="col-modulo-{{ $modulo->id }}"
                                data-modulo="{{ $modulo->id }}">
                            <label class="custom-control-label font-weight-bold" for="col-modulo-{{ $modulo->id }}">
                                {{ $modulo->nombre }}
                            </label>
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($permisos as $permiso)
                <tr>
                    <th scope="row">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input js-fila" id="fila-permiso-{{ $permiso->id }}"
                                data-permiso="{{ $permiso->id }}">
                            <label class="custom-control-label" for="fila-permiso-{{ $permiso->id }}">
                                {{ $permiso->nombre }}
                            </label>
                        </div>
                        @if ($permiso->descripcion)
                            <small class="emp-ayuda d-block pl-4">{{ $permiso->descripcion }}</small>
                        @endif
                    </th>
                    @foreach ($modulos as $modulo)
                        @php $marcado = in_array((int) $permiso->id, array_map('intval', $seleccion->get($modulo->id, []))); @endphp
                        <td>
                            <div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input js-celda"
                                    id="mod{{ $modulo->id }}perm{{ $permiso->id }}"
                                    name="modulos[{{ $modulo->id }}][]" value="{{ $permiso->id }}"
                                    data-modulo="{{ $modulo->id }}" data-permiso="{{ $permiso->id }}"
                                    {{ $marcado ? 'checked' : '' }}>
                                <label class="custom-control-label" for="mod{{ $modulo->id }}perm{{ $permiso->id }}">
                                    <span class="sr-only">{{ $permiso->nombre }} en {{ $modulo->nombre }}</span>
                                </label>
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<p class="mt-2 mb-4">
    <span class="emp-contador" id="contadorPermisos">{{ $totalAsignados }}</span>
    <span class="emp-ayuda">permisos asignados</span>
</p>

<h5 class="emp-seccion">Permisos especiales</h5>
<div class="row">
    @foreach ($catalogoEspeciales as $codigo => $descripcion)
        <div class="col-12 col-md-6">
            <div class="custom-control custom-checkbox mb-2">
                <input class="custom-control-input" type="checkbox" id="pe_{{ $codigo }}" name="permisos_especiales[]"
                    value="{{ $codigo }}" {{ in_array($codigo, $especiales, true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="pe_{{ $codigo }}">{{ $descripcion }}</label>
            </div>
        </div>
    @endforeach
</div>

@push('js')
<script>
    (function () {
        var matriz = document.getElementById('matrizPermisos');
        if (!matriz) return;

        var celdas = Array.prototype.slice.call(matriz.querySelectorAll('.js-celda'));
        var columnas = Array.prototype.slice.call(matriz.querySelectorAll('.js-columna'));
        var filas = Array.prototype.slice.call(matriz.querySelectorAll('.js-fila'));
        var contador = document.getElementById('contadorPermisos');

        // Estado de cada cabecera: marcada si todas sus celdas lo están, indeterminada si algunas.
        function estadoCabecera(cabecera, claveAtributo) {
            var valor = cabecera.getAttribute(claveAtributo === 'modulo' ? 'data-modulo' : 'data-permiso');
            var grupo = celdas.filter(function (c) { return c.getAttribute('data-' + claveAtributo) === valor; });
            var marcados = grupo.filter(function (c) { return c.checked; }).length;
            cabecera.checked = grupo.length > 0 && marcados === grupo.length;
            cabecera.indeterminate = marcados > 0 && marcados < grupo.length;
        }

        function actualizar() {
            contador.textContent = celdas.filter(function (c) { return c.checked; }).length;
            columnas.forEach(function (c) { estadoCabecera(c, 'modulo'); });
            filas.forEach(function (f) { estadoCabecera(f, 'permiso'); });
        }

        matriz.addEventListener('change', function (e) {
            var t = e.target;
            if (t.classList.contains('js-columna')) {
                celdas.forEach(function (c) {
                    if (c.getAttribute('data-modulo') === t.getAttribute('data-modulo')) c.checked = t.checked;
                });
            } else if (t.classList.contains('js-fila')) {
                celdas.forEach(function (c) {
                    if (c.getAttribute('data-permiso') === t.getAttribute('data-permiso')) c.checked = t.checked;
                });
            }
            actualizar();
        });

        actualizar();
    })();
</script>
@endpush
