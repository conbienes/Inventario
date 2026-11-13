/*
 * Búsqueda rápida en tablas sin paginación (CONBIENES)
 * ------------------------------------------------------------------
 * Para cualquier <table data-filtro-rapido> con más de 5 filas, agrega encima un campo
 * "Buscar en esta tabla…" que oculta al escribir las filas que no coinciden.
 * No recarga la página ni toca los datos: las filas ocultas se siguen enviando en los formularios.
 */
(function () {
    'use strict';

    function normalizar(t) {
        return (t || '').toString().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    }

    function preparar(tabla) {
        var cuerpo = tabla.tBodies[0];
        if (!cuerpo || cuerpo.rows.length <= 5) return;

        var caja = document.createElement('div');
        caja.className = 'cb-tabla-rapida';
        caja.innerHTML =
            '<i class="fas fa-search" aria-hidden="true"></i>' +
            '<input type="search" class="form-control" placeholder="Buscar en esta tabla…" aria-label="Buscar en esta tabla">' +
            '<span class="cb-tabla-rapida__cuenta"></span>';

        var contenedor = tabla.closest('.table-responsive') || tabla;
        contenedor.parentNode.insertBefore(caja, contenedor);

        var input = caja.querySelector('input');
        var cuenta = caja.querySelector('.cb-tabla-rapida__cuenta');
        var filas = Array.prototype.slice.call(cuerpo.rows);
        var vacia = document.createElement('tr');
        vacia.innerHTML = '<td colspan="99" class="text-center text-muted py-4">Ninguna fila coincide con la búsqueda.</td>';

        function filtrar() {
            var q = normalizar(input.value.trim());
            var visibles = 0;
            filas.forEach(function (fila) {
                var texto = normalizar(fila.textContent + ' ' + Array.prototype.map.call(
                    fila.querySelectorAll('input:not([type=hidden]), textarea, select'), function (c) { return c.value; }).join(' '));
                var ok = q === '' || texto.indexOf(q) !== -1;
                fila.style.display = ok ? '' : 'none';
                if (ok) visibles++;
            });
            if (visibles === 0 && !vacia.parentNode) cuerpo.appendChild(vacia);
            if (visibles > 0 && vacia.parentNode) vacia.parentNode.removeChild(vacia);
            cuenta.textContent = q === '' ? filas.length + ' filas' : visibles + ' de ' + filas.length;
        }

        input.addEventListener('input', filtrar);
        // Enter no debe enviar el formulario que contenga la tabla
        input.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
        filtrar();
    }

    function iniciar() {
        Array.prototype.forEach.call(document.querySelectorAll('table[data-filtro-rapido]'), preparar);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();
})();
