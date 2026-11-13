{{-- Estilos comunes de la gestion de empleados y permisos (incluir una sola vez por pagina). --}}
@push('css')
<style>
    :root {
        --emp-primario: #005484;
        --emp-primario-oscuro: #00426a;
        --emp-exito: #15803D;
        --emp-alerta: #B45309;
        --emp-peligro: #B91C1C;
        --emp-texto: #1F2937;
        --emp-texto-2: #4B5563;
        --emp-borde: #D1D5DB;
        --emp-fondo-suave: #F9FAFB;
        --emp-activo-fondo: #DCFCE7;
        --emp-activo-texto: #166534;
        --emp-inactivo-fondo: #E5E7EB;
        --emp-inactivo-texto: #374151;
    }

    .emp-page { color: var(--emp-texto); }
    .emp-page .card-primary.card-outline { border-top-color: var(--emp-primario); }
    .emp-titulo { color: var(--emp-primario); font-weight: 600; font-size: 1.35rem; }
    .emp-ayuda { color: var(--emp-texto-2); font-size: .875rem; }

    .emp-seccion {
        font-size: 1rem;
        font-weight: 700;
        color: var(--emp-primario);
        border-bottom: 2px solid var(--emp-primario);
        padding-bottom: .35rem;
        margin: 1.5rem 0 1rem;
    }
    .emp-seccion:first-child { margin-top: 0; }

    .emp-page .btn-emp-primario {
        background-color: var(--emp-primario);
        border-color: var(--emp-primario);
        color: #fff;
        font-weight: 600;
    }
    .emp-page .btn-emp-primario:hover,
    .emp-page .btn-emp-primario:focus {
        background-color: var(--emp-primario-oscuro);
        border-color: var(--emp-primario-oscuro);
        color: #fff;
    }

    .emp-badge { font-size: .8rem; font-weight: 600; padding: .35em .7em; border-radius: 999px; }
    .emp-badge-activo { background: var(--emp-activo-fondo); color: var(--emp-activo-texto); }
    .emp-badge-inactivo { background: var(--emp-inactivo-fondo); color: var(--emp-inactivo-texto); }
    .emp-badge-vigente { background: var(--emp-activo-fondo); color: var(--emp-activo-texto); }
    .emp-badge-pendiente { background: #FEF3C7; color: #92400E; }
    .emp-badge-terminado { background: var(--emp-inactivo-fondo); color: var(--emp-inactivo-texto); }
    .emp-badge-anulado { background: #FEE2E2; color: #991B1B; }
    .emp-badge-modulo { background: #fff; color: var(--emp-primario); border: 1px solid var(--emp-primario); font-weight: 500; }

    /* Matriz de permisos: filas = permisos, columnas = modulos */
    .emp-matriz-wrap { border: 1px solid var(--emp-borde); border-radius: .25rem; }
    .emp-matriz { margin-bottom: 0; }
    .emp-matriz th, .emp-matriz td { vertical-align: middle; text-align: center; }
    .emp-matriz thead th { background: var(--emp-fondo-suave); font-size: .9rem; color: var(--emp-texto); }
    .emp-matriz tbody th { background: #fff; text-align: left; font-weight: 500; }
    .emp-matriz .emp-celda-nombre { text-align: left; min-width: 14rem; }
    .emp-contador { font-weight: 600; color: var(--emp-primario); }

    /* Fuerza de contrasena */
    .emp-fuerza { height: 6px; background: #E5E7EB; border-radius: 3px; overflow: hidden; margin-top: .5rem; }
    .emp-fuerza-barra { height: 100%; width: 0; transition: width .25s, background-color .25s; }
    .emp-fuerza-barra.is-debil { background: var(--emp-peligro); }
    .emp-fuerza-barra.is-media { background: var(--emp-alerta); }
    .emp-fuerza-barra.is-fuerte { background: var(--emp-exito); }
    .emp-txt-ok { color: var(--emp-exito); }
    .emp-txt-error { color: var(--emp-peligro); }
</style>
@endpush
