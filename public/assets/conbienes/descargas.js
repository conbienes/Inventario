/*
 * Aviso de descargas (CONBIENES)
 * ------------------------------------------------------------------
 * Cuando el usuario pide un informe (Excel, plantilla...) muestra un aviso
 * "Generando informe…" y lo cambia a "¡Listo!" cuando el archivo sale del servidor.
 *
 * Detecta solo:
 *   - enlaces y formularios cuya URL contiene "exportar" o "plantilla", y
 *   - cualquier enlace, formulario o botón con el atributo data-descarga.
 * Para excluir uno: data-descarga="no".
 *
 * El servidor confirma con la cookie "descarga_token" (App\Http\Middleware\MarcarDescarga).
 */
(function () {
    'use strict';

    var PATRON = /exportar|plantilla/i;
    var COOKIE = 'descarga_token';
    var LENTO_MS = 30000;      // a los 30 s avisa que puede tardar
    var LIMITE_MS = 180000;    // a los 3 min deja de esperar

    var aviso = null;
    var temporizador = null;

    function nuevoToken() {
        return (Date.now().toString(36) + Math.random().toString(36).slice(2, 10)).replace(/[^a-z0-9]/gi, '');
    }

    function esDescarga(el, url) {
        var marca = el && el.getAttribute && el.getAttribute('data-descarga');
        if (marca === 'no') return false;
        if (marca !== null && marca !== undefined) return true;
        if (!url) return false;
        try {
            var u = new URL(url, window.location.href);
            return u.origin === window.location.origin && PATRON.test(u.pathname);
        } catch (e) {
            return false;
        }
    }

    function leerCookie(nombre) {
        var partes = document.cookie ? document.cookie.split('; ') : [];
        for (var i = 0; i < partes.length; i++) {
            var p = partes[i].split('=');
            if (decodeURIComponent(p[0]) === nombre) return decodeURIComponent(p.slice(1).join('='));
        }
        return null;
    }

    function borrarCookie(nombre) {
        document.cookie = nombre + '=; Max-Age=0; path=/';
    }

    /* ---------- Aviso visual ---------- */
    function crearAviso() {
        if (aviso) return aviso;
        aviso = document.createElement('div');
        aviso.className = 'cb-descarga';
        aviso.setAttribute('role', 'status');
        aviso.setAttribute('aria-live', 'polite');
        aviso.innerHTML =
            '<div class="cb-descarga__icono"><span class="cb-descarga__spinner"></span><i class="fas fa-check"></i><i class="fas fa-exclamation"></i></div>' +
            '<div class="cb-descarga__texto"><strong></strong><span></span></div>' +
            '<button type="button" class="cb-descarga__cerrar" aria-label="Cerrar aviso">&times;</button>' +
            '<div class="cb-descarga__barra"><span></span></div>';
        aviso.querySelector('.cb-descarga__cerrar').addEventListener('click', ocultar);
        document.body.appendChild(aviso);
        return aviso;
    }

    function mostrar(estado, titulo, detalle) {
        var a = crearAviso();
        a.classList.remove('is-cargando', 'is-listo', 'is-alerta');
        a.classList.add('is-' + estado, 'is-visible');
        a.querySelector('strong').textContent = titulo;
        a.querySelector('.cb-descarga__texto span').textContent = detalle;
    }

    function ocultar() {
        if (aviso) aviso.classList.remove('is-visible');
        detener();
    }

    function detener() {
        if (temporizador) clearInterval(temporizador);
        temporizador = null;
    }

    function esperar(token) {
        detener();
        borrarCookie(COOKIE);
        mostrar('cargando', 'Generando informe…', 'Estamos preparando el archivo. La descarga empezará en un momento.');

        var inicio = Date.now();
        var avisoLento = false;

        temporizador = setInterval(function () {
            if (leerCookie(COOKIE) === token) {
                borrarCookie(COOKIE);
                detener();
                mostrar('listo', '¡Descarga lista!', 'Revisa tu carpeta de Descargas.');
                setTimeout(function () {
                    if (aviso && aviso.classList.contains('is-listo')) ocultar();
                }, 4500);
                return;
            }

            var pasado = Date.now() - inicio;
            if (!avisoLento && pasado > LENTO_MS) {
                avisoLento = true;
                mostrar('cargando', 'Sigue generándose…', 'Con muchos datos puede tardar un poco más. No cierres esta página.');
            }
            if (pasado > LIMITE_MS) {
                detener();
                mostrar('alerta', 'No pudimos confirmar la descarga', 'Revisa tu carpeta de Descargas o inténtalo de nuevo.');
            }
        }, 400);
    }

    /* ---------- Enlaces ---------- */
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var enlace = e.target.closest && e.target.closest('a[href]');
        if (!enlace) return;
        var href = enlace.getAttribute('href');
        if (!href || href.charAt(0) === '#' || /^javascript:/i.test(href)) return;
        if (!esDescarga(enlace, enlace.href)) return;

        var token = nuevoToken();
        var u = new URL(enlace.href, window.location.href);
        u.searchParams.set(COOKIE, token);
        enlace.href = u.toString();

        // Si otro script cancela el clic, no mostramos nada
        setTimeout(function () { if (!e.defaultPrevented) esperar(token); }, 0);
    });

    /* ---------- Formularios ---------- */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        var boton = e.submitter || null;
        var accion = (boton && boton.getAttribute('formaction')) ? boton.formAction : form.action;
        var marcado = (boton && boton.hasAttribute('data-descarga')) ? boton : form;
        if (!esDescarga(marcado, accion)) return;

        var token = nuevoToken();
        var campo = form.querySelector('input[name="' + COOKIE + '"]');
        if (!campo) {
            campo = document.createElement('input');
            campo.type = 'hidden';
            campo.name = COOKIE;
            form.appendChild(campo);
        }
        campo.value = token;

        setTimeout(function () { if (!e.defaultPrevented) esperar(token); }, 0);
    });
})();
