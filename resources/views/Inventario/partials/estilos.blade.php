{{-- Estilos comunes del modulo Inventario (incluir una sola vez por pagina, la clase inv-page va en el body). --}}
@push('css')
<style>
    :root {
        --inv-primario: #005484;
        --inv-primario-oscuro: #00426a;
        --inv-exito: #15803D;
        --inv-exito-oscuro: #166534;
        --inv-texto: #1F2937;
        --inv-texto-2: #4B5563;
        --inv-ambar: #B45309;
        --inv-borde-suave: #E5E7EB;
    }

    .inv-page .btn-success {
        background-color: var(--inv-exito);
        border-color: var(--inv-exito);
        color: #fff;
    }
    .inv-page .btn-success:hover,
    .inv-page .btn-success:focus {
        background-color: var(--inv-exito-oscuro);
        border-color: var(--inv-exito-oscuro);
        color: #fff;
    }
    .inv-page .badge-success,
    .inv-page .bg-success {
        background-color: var(--inv-exito) !important;
        color: #fff;
    }
    .inv-page .bg-info {
        background-color: var(--inv-primario) !important;
        color: #fff;
    }
    .inv-page .inv-header-primario {
        background-color: var(--inv-primario);
        color: #fff;
    }
    .inv-page .inv-header-primario h5 { color: #fff; }

    @media (max-width: 575.98px) {
        .inv-page .table-responsive .table td,
        .inv-page .table-responsive .table th {
            padding: .35rem .4rem;
            font-size: .875rem;
        }
        .inv-page .card-title { font-size: 1.05rem; }
    }

    /* Botón primario y de cuenta con el azul corporativo (Bootstrap usa #007bff por defecto) */
    .inv-page .btn-primary,
    .inv-page .btn-primary:disabled {
        background-color: var(--inv-primario);
        border-color: var(--inv-primario);
        color: #fff;
    }
    .inv-page .btn-primary:hover,
    .inv-page .btn-primary:focus {
        background-color: var(--inv-primario-oscuro);
        border-color: var(--inv-primario-oscuro);
        color: #fff;
    }
    .inv-page .btn-primary:disabled { opacity: .55; }

    /* Cabeceras de tarjeta azules: mismo azul corporativo (Bootstrap usa #007bff) */
    .inv-page .card-primary:not(.card-outline) > .card-header {
        background-color: var(--inv-primario);
        color: #fff;
    }
    .inv-page .card-primary.card-outline { border-top-color: var(--inv-primario); }

    /* Menu lateral: texto claro sobre el azul corporativo (antes gris apagado) */
    .main-sidebar .nav-sidebar .nav-link,
    .main-sidebar .nav-sidebar .nav-link p,
    .main-sidebar .nav-sidebar .nav-link i { color: #fff; }
    .main-sidebar .nav-sidebar .nav-link.active {
        background-color: rgba(255, 255, 255, .15);
        color: #fff;
    }
    .main-sidebar .nav-sidebar .nav-treeview .nav-link.active {
        background-color: rgba(255, 255, 255, .25);
    }

    /* ---- Utilidades del modulo (usar estas clases, no colores sueltos) ---- */
    .inv-page .btn-outline-primary {
        color: var(--inv-primario);
        border-color: var(--inv-primario);
    }
    .inv-page .btn-outline-primary:hover,
    .inv-page .btn-outline-primary:focus {
        background-color: var(--inv-primario);
        border-color: var(--inv-primario);
        color: #fff;
    }
    .inv-page .inv-tarjeta-enlace {
        color: var(--inv-texto);
        text-decoration: none;
        border: 1px solid #E5E7EB;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .inv-page .inv-tarjeta-enlace:hover,
    .inv-page .inv-tarjeta-enlace:focus {
        color: var(--inv-primario-oscuro);
        border-color: var(--inv-primario);
        box-shadow: 0 0 0 .15rem rgba(0, 84, 132, .15);
        text-decoration: none;
    }
    .inv-page .inv-texto-2 { color: var(--inv-texto-2); }
    .inv-page .inv-texto { color: var(--inv-texto); }
    .inv-page .inv-acciones {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        align-items: center;
    }
    .inv-page .inv-acciones--fin { justify-content: flex-end; }
    .inv-page .inv-pie {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        justify-content: space-between;
        align-items: center;
    }
    /* Tablas: el estilo general está en assets/conbienes/tema.css */
    /* Formularios: etiquetas visibles y con jerarquia */
    .inv-page .form-group label,
    .inv-page label { font-weight: 600; color: var(--inv-texto); }
    .inv-page .inv-ayuda { font-size: .875rem; color: var(--inv-texto-2); }
    .inv-page .inv-campo-obligatorio { color: #B91C1C; }
    /* Alertas (ambar) */
    .inv-page .inv-alerta-ambar {
        background-color: #FEF3C7;
        border: 1px solid #F59E0B;
        color: var(--inv-ambar);
    }
    /* Estado vacio de tablas y listas */
    .inv-page .inv-vacio {
        text-align: center;
        color: var(--inv-texto-2);
        padding: 1.5rem 0;
        margin: 0;
    }
    /* Calificacion con estrellas (radios ocultos + etiquetas) */
    .inv-page .inv-estrellas {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
    }
    .inv-page .inv-estrellas input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
    }
    .inv-page .inv-estrellas label {
        font-size: 2rem;
        line-height: 1;
        font-weight: 400;
        color: #D1D5DB;
        cursor: pointer;
        margin: 0;
    }
    .inv-page .inv-estrellas label.inv-estrella-activa { color: var(--inv-ambar); }
    .inv-page .inv-estrellas input[type="radio"]:focus-visible + label {
        outline: 2px solid var(--inv-primario);
        outline-offset: 2px;
        border-radius: .25rem;
    }

    /* =========================================================
       ESTÁNDAR DE INVENTARIO (todas las pantallas usan estas clases)
       - Cabecera:   <div class="inv-cabecera"> <div><h1 class="inv-titulo">…</h1><p class="inv-subtitulo">…</p></div> <div class="inv-acciones">botones</div> </div>
       - Filtros:    <form class="inv-filtros"> … </form>  (campo + botón Buscar + enlace Limpiar)
       - Tabla:      <div class="inv-tabla-wrap"><table class="table inv-tabla">…</table></div>
       - Paginación: ->links('pagination::bootstrap-4') (Blade) dentro de <div class="inv-paginacion">
       - Vacío:      <div class="inv-vacio"><i class="fas fa-…"></i><p>…</p></div>
       - Estado:     <span class="inv-estado inv-estado--ok|alerta|critico|neutro">Texto</span>
       - Tarjeta:    <div class="inv-tarjeta"> <h2>…</h2> … </div>
       ========================================================= */
    .inv-page .inv-cabecera { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: .75rem 1rem; margin-bottom: 1.25rem; }
    .inv-page .inv-titulo { font-size: 1.5rem; font-weight: 700; color: var(--inv-texto); margin: 0; line-height: 1.2; }
    .inv-page .inv-subtitulo { margin: .25rem 0 0; color: var(--inv-texto-2); font-size: .95rem; }
    .inv-page .inv-acciones { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }

    .inv-page .btn { border-radius: .4rem; font-weight: 600; }
    .inv-page .btn-sm { font-weight: 600; }

    .inv-page .inv-filtros {
        display: flex; flex-wrap: wrap; gap: .5rem; align-items: center;
        background: #fff; border: 1px solid #E5E7EB; border-radius: .6rem;
        padding: .75rem; margin-bottom: 1rem;
    }
    .inv-page .inv-filtros .form-control { min-width: 220px; }

    .inv-page .inv-tarjeta-wrap, .inv-page .inv-tabla-wrap {
        background: #fff; border: 1px solid #E5E7EB; border-radius: .6rem; overflow: hidden;
    }
    .inv-page .inv-tabla { margin-bottom: 0; }
    .inv-page .inv-tabla td.num, .inv-page .inv-tabla th.num { text-align: right; font-variant-numeric: tabular-nums; }

    .inv-page .inv-paginacion { display: flex; justify-content: center; margin-top: 1rem; }
    .inv-page .inv-paginacion .page-link { color: var(--inv-primario); border-color: #D1D5DB; }
    .inv-page .inv-paginacion .page-item.active .page-link { background: var(--inv-primario); border-color: var(--inv-primario); color: #fff; }
    .inv-page .inv-paginacion .page-item.disabled .page-link { color: #9CA3AF; }

    .inv-page .inv-vacio { text-align: center; padding: 2.5rem 1rem; color: var(--inv-texto-2); }
    .inv-page .inv-vacio i { font-size: 2rem; color: #9CA3AF; margin-bottom: .5rem; }
    .inv-page .inv-vacio p { margin: 0; font-size: .95rem; }

    .inv-page .inv-estado {
        display: inline-block; font-size: .78rem; font-weight: 600;
        padding: .2rem .6rem; border-radius: 999px; white-space: nowrap;
    }
    .inv-page .inv-estado--ok { background: #DCFCE7; color: #166534; }
    .inv-page .inv-estado--alerta { background: #FEF3C7; color: #92400E; }
    .inv-page .inv-estado--critico { background: #FEE2E2; color: #991B1B; }
    .inv-page .inv-estado--neutro { background: #E5E7EB; color: #374151; }

    /* =========================================================
       TEMA DE INVENTARIO: superficies, menú y cabecera modernos
       (solo páginas con body.inv-page; el motor sigue siendo AdminLTE)
       ========================================================= */
    .inv-page {
        background: #F1F5F9;
        font-family: "Source Sans 3", "Source Sans Pro", "Segoe UI", system-ui, sans-serif;
        color: var(--inv-texto);
    }
    .inv-page .content-wrapper { background: #F1F5F9; }
    .inv-page .content { padding: 0 1rem 1rem; }
    .inv-page .content-wrapper > .content:first-child { padding-top: 1.25rem; }

    /* Cabecera superior */
    .inv-page .main-header.navbar {
        background: #FFFFFF;
        border-bottom: 1px solid #E2E8F0;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        min-height: 60px;
    }
    .inv-page .main-header .navbar-nav .nav-link { color: var(--inv-texto-2); }
    .inv-page .main-header .btn-primary {
        border-radius: 999px;
        padding: .45rem 1rem;
        box-shadow: 0 2px 6px rgba(0, 84, 132, .25);
    }

    /* Menú lateral */
    .inv-page .main-sidebar {
        background: linear-gradient(180deg, #003F63 0%, #005484 100%);
        box-shadow: 4px 0 18px rgba(15, 23, 42, .12);
    }
    .inv-page .brand-link { border-bottom: 1px solid rgba(255, 255, 255, .12); padding: .9rem .75rem; }
    .inv-page .sidebar { padding-top: .5rem; }
    .inv-page .nav-sidebar > .nav-item { margin: .15rem .6rem; }
    .inv-page .nav-sidebar > .nav-item > .nav-link {
        border-radius: .6rem;
        padding: .6rem .75rem;
        font-weight: 600;
        font-size: .95rem;
        transition: background-color .15s ease;
    }
    .inv-page .nav-sidebar > .nav-item > .nav-link:hover { background: rgba(255, 255, 255, .10); }
    .inv-page .nav-sidebar > .nav-item > .nav-link.active {
        background: rgba(255, 255, 255, .16);
        box-shadow: inset 3px 0 0 #7DD3FC;
    }
    .inv-page .nav-sidebar .nav-treeview { margin: .15rem 0 .35rem 1.1rem; border-left: 1px solid rgba(255, 255, 255, .15); padding-left: .4rem; }
    .inv-page .nav-sidebar .nav-treeview > .nav-item > .nav-link {
        border-radius: .5rem; font-weight: 500; font-size: .9rem; padding: .45rem .6rem;
    }
    .inv-page .nav-sidebar .nav-treeview > .nav-item > .nav-link.active { background: rgba(255, 255, 255, .18); }

    /* Tarjetas y bloques */
    .inv-page .card,
    .inv-page .inv-tarjeta,
    .inv-page .inv-tarjeta-wrap,
    .inv-page .inv-tabla-wrap {
        border-radius: 14px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .05), 0 4px 12px rgba(15, 23, 42, .03);
    }
    .inv-page .card { overflow: hidden; }
    .inv-page .card-header { background: #FFFFFF; border-bottom: 1px solid #E2E8F0; }

    /* Botones */
    .inv-page .btn { border-radius: 10px; }
    .inv-page .btn-success, .inv-page .btn-primary { box-shadow: 0 2px 6px rgba(0, 84, 132, .18); }

    /* Titulares */
    .inv-page .inv-titulo { letter-spacing: -.01em; }


    /* =========================================================
       COMPONENTES DE FLUJO (Entradas de almacén, Arqueo)
       ========================================================= */
    /* Encabezado destacado */
    .inv-page .inv-hero {
        position: relative; overflow: hidden; display: flex; align-items: center; gap: 1.25rem;
        padding: 1.5rem 1.75rem; margin-bottom: 1.25rem; border-radius: 18px; color: #fff;
        background:
            radial-gradient(circle at 92% 0%, rgba(255, 255, 255, .14) 0, transparent 38%),
            linear-gradient(135deg, #003F63 0%, #005484 55%, #0A6AA0 100%);
        box-shadow: 0 14px 34px rgba(0, 63, 99, .22);
    }
    .inv-page .inv-hero__ico {
        flex: none; width: 58px; height: 58px; border-radius: 16px; display: grid; place-items: center;
        background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .25); font-size: 1.5rem;
    }
    .inv-page .inv-hero__txt { flex: 1 1 auto; min-width: 0; }
    .inv-page .inv-hero__etiqueta { font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #BFDBFE; }
    .inv-page .inv-hero h1 { font-size: 1.6rem; font-weight: 800; margin: .1rem 0 .2rem; color: #fff; letter-spacing: -.01em; }
    .inv-page .inv-hero p { margin: 0; color: #D5E7F3; }
    .inv-page .inv-hero .btn-light { color: #003F63; font-weight: 700; }
    .inv-page .inv-hero .btn-outline-light:hover { color: #003F63; }
    @media (max-width: 575.98px) { .inv-page .inv-hero { flex-wrap: wrap; padding: 1.25rem; } .inv-page .inv-hero__ico { display: none; } }

    /* Pasos */
    .inv-page .inv-pasos { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; list-style: none; padding: 0; margin: 0 0 1.25rem; }
    .inv-page .inv-paso { display: flex; align-items: center; gap: .5rem; font-weight: 600; font-size: .92rem; color: #64748B; }
    .inv-page .inv-paso + .inv-paso::before { content: ""; width: 36px; height: 2px; background: #CBD5E1; border-radius: 2px; margin-right: .1rem; }
    .inv-page .inv-paso__num {
        width: 30px; height: 30px; border-radius: 50%; display: inline-grid; place-items: center;
        background: #E2E8F0; color: #475569; font-size: .85rem; font-weight: 700;
    }
    .inv-page .inv-paso.is-activo { color: var(--inv-primario); }
    .inv-page .inv-paso.is-activo .inv-paso__num { background: var(--inv-primario); color: #fff; box-shadow: 0 0 0 4px rgba(0, 84, 132, .15); }
    .inv-page .inv-paso.is-hecho { color: var(--inv-exito); }
    .inv-page .inv-paso.is-hecho .inv-paso__num { background: #DCFCE7; color: var(--inv-exito); }
    .inv-page .inv-paso.is-hecho + .inv-paso::before { background: #86EFAC; }

    /* Resumen en chips (datos del pedido) */
    .inv-page .inv-resumen { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.25rem; }
    .inv-page .inv-dato {
        display: flex; flex-direction: column; padding: .55rem .9rem; min-width: 130px;
        background: #fff; border: 1px solid #E2E8F0; border-radius: 12px;
    }
    .inv-page .inv-dato small { font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #64748B; }
    .inv-page .inv-dato strong { font-size: 1rem; color: var(--inv-texto); }

    /* Título de sección dentro de tarjetas */
    .inv-page .inv-seccion {
        display: flex; align-items: center; gap: .5rem; font-size: .8rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: var(--inv-primario); margin: 1.25rem 0 .75rem;
    }
    .inv-page .inv-seccion:first-child { margin-top: 0; }

    /* Opciones tipo tarjeta (radios) */
    .inv-page .inv-opciones { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .75rem; }
    .inv-page .inv-opcion { position: relative; margin: 0; }
    .inv-page .inv-opcion input { position: absolute; opacity: 0; pointer-events: none; }
    .inv-page .inv-opcion label {
        display: flex; gap: .75rem; align-items: flex-start; height: 100%; margin: 0; cursor: pointer;
        padding: .9rem 1rem; border: 1.5px solid #CBD5E1; border-radius: 14px; background: #fff; font-weight: 400;
        transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
    }
    .inv-page .inv-opcion label:hover { border-color: #93C5FD; }
    .inv-page .inv-opcion__ico { flex: none; width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; font-size: 1.05rem; }
    .inv-page .inv-opcion strong { display: block; font-size: 1rem; color: var(--inv-texto); }
    .inv-page .inv-opcion small { display: block; color: var(--inv-texto-2); font-size: .85rem; line-height: 1.3; }
    .inv-page .inv-opcion input:checked + label { border-color: var(--inv-primario); background: #F0F8FF; box-shadow: 0 0 0 3px rgba(0, 84, 132, .14); }
    .inv-page .inv-opcion input:focus-visible + label { outline: 3px solid rgba(0, 84, 132, .35); outline-offset: 2px; }

    /* Zona para subir archivos */
    .inv-page .inv-archivo {
        position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .35rem;
        padding: 1.5rem 1rem; text-align: center; cursor: pointer; margin: 0;
        border: 2px dashed #93C5FD; border-radius: 14px; background: #F8FBFF; color: var(--inv-texto-2);
        transition: border-color .15s ease, background-color .15s ease; font-weight: 400;
    }
    .inv-page .inv-archivo:hover, .inv-page .inv-archivo.is-encima { border-color: var(--inv-primario); background: #EEF6FD; }
    .inv-page .inv-archivo.tiene-archivo { border-style: solid; border-color: #86EFAC; background: #F0FDF4; }
    .inv-page .inv-archivo i { font-size: 1.8rem; color: var(--inv-primario); }
    .inv-page .inv-archivo.tiene-archivo i { color: var(--inv-exito); }
    .inv-page .inv-archivo strong { color: var(--inv-texto); }
    .inv-page .inv-archivo input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }

    /* Barra de avance pequeña */
    .inv-page .inv-avance { height: 6px; background: #E5E7EB; border-radius: 4px; overflow: hidden; min-width: 80px; }
    .inv-page .inv-avance > span { display: block; height: 100%; background: var(--inv-exito); border-radius: 4px; }

    /* Tarjeta de contenido (común a todo el módulo) */
    .inv-page .inv-tarjeta { background: #fff; padding: 1.1rem 1.2rem; }
    .inv-page .inv-tarjeta h2 { font-size: 1.05rem; font-weight: 700; color: var(--inv-texto); margin: 0 0 .75rem; }
    .inv-page .inv-tarjeta-wrap > .inv-tarjeta, .inv-page .inv-tarjeta-wrap > form.inv-tarjeta { border: 0; box-shadow: none; }

    /* Barra de filtros (partials/filtros.blade.php) */
    .inv-page .inv-filtrobar {
        display: flex; flex-wrap: wrap; align-items: flex-end; gap: .75rem;
        background: #fff; border: 1px solid #E2E8F0; border-radius: 14px; padding: .9rem 1rem; margin-bottom: .75rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .05);
    }
    .inv-page .inv-filtrobar__campo { display: flex; flex-direction: column; min-width: 160px; flex: 1 1 160px; position: relative; }
    .inv-page .inv-filtrobar__campo label { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #64748B; margin-bottom: .25rem; }
    .inv-page .inv-filtrobar__campo--texto { flex: 3 1 260px; justify-content: flex-end; }
    .inv-page .inv-filtrobar__campo--texto i { position: absolute; left: .9rem; bottom: .85rem; color: #94A3B8; pointer-events: none; }
    .inv-page .inv-filtrobar__campo--texto .form-control { padding-left: 2.4rem; }
    .inv-page .inv-filtrobar__botones { display: flex; gap: .5rem; }
    .inv-page .inv-filtrobar__estado { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin: 0 0 1rem; }
    .inv-page .inv-filtrobar__total { font-weight: 700; color: var(--inv-texto); margin-right: .35rem; font-size: .9rem; }
    .inv-page .inv-chip-filtro {
        display: inline-flex; align-items: center; padding: .25rem .7rem; border-radius: 999px; font-size: .82rem; font-weight: 600;
        background: #E0F2FE; color: #0C4A6E; text-decoration: none !important; border: 1px solid #BAE6FD;
    }
    .inv-page .inv-chip-filtro:hover { background: #FEE2E2; border-color: #FECACA; color: #991B1B; }
    .inv-page .inv-chip-filtro--limpiar { background: #fff; border-color: #CBD5E1; color: #475569; }
    @media (max-width: 575.98px) { .inv-page .inv-filtrobar__campo, .inv-page .inv-filtrobar__botones { flex: 1 1 100%; } .inv-page .inv-filtrobar__botones .btn { width: 100%; } }
</style>
@endpush
