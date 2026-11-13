{{-- Paleta y utilidades comunes de Bono Regalo (Bootstrap 4 / AdminLTE 3).
     Incluir dentro de @push('css') en cada vista del módulo. --}}
<style>
  :root {
    --br-primary: #005484;      /* texto blanco 8.07:1 */
    --br-primary-dark: #003f63;
    --br-success: #15803D;
    --br-warning: #B45309;
    --br-danger: #B91C1C;
    --br-info: #0E7490;
    --br-text: #1F2937;
    --br-muted: #4B5563;
    --br-border: #D1D5DB;
    --br-soft: #F3F4F6;
    --br-highlight: #E0F2FE;
    --br-yellow: #FFC107;       /* amarillo: siempre con texto oscuro */
  }

  .bg-primary, .bg-gradient-primary { background-color: var(--br-primary) !important; color: #fff; }
  .text-primary { color: var(--br-primary) !important; }
  .btn-primary { background-color: var(--br-primary); border-color: var(--br-primary); color: #fff; }
  .btn-primary:hover, .btn-primary:focus { background-color: var(--br-primary-dark); border-color: var(--br-primary-dark); color: #fff; }
  .btn-outline-primary { color: var(--br-primary); border-color: var(--br-primary); }
  .btn-outline-primary:hover { background-color: var(--br-primary); color: #fff; }
  .btn-success, .bg-success { background-color: var(--br-success); border-color: var(--br-success); color: #fff; }
  .text-success { color: var(--br-success) !important; }
  .btn-danger, .bg-danger { background-color: var(--br-danger); border-color: var(--br-danger); color: #fff; }
  .text-danger { color: var(--br-danger) !important; }
  .btn-warning, .bg-warning { background-color: var(--br-yellow); border-color: var(--br-yellow); color: var(--br-text); }
  .text-warning { color: var(--br-warning) !important; }
  .btn-info, .bg-info { background-color: var(--br-info); border-color: var(--br-info); color: #fff; }
  .text-info { color: var(--br-info) !important; }
  .text-muted { color: var(--br-muted) !important; }
  .text-body, body { color: var(--br-text); }

  /* Fondos suaves para iconos y etiquetas (reemplazan a bg-*-bg-opacity-10) */
  .br-tint-primary, .br-tint-blue { background-color: #E6F0F5; }
  .br-tint-success { background-color: #E7F6EC; }
  .br-tint-info { background-color: #E0F7FA; }
  .br-tint-secondary { background-color: var(--br-soft); }

  /* Utilidades de espacio entre hijos de flex (Bootstrap 5) */
  .gap-1 { gap: .25rem; }
  .gap-2 { gap: .5rem; }
  .gap-3 { gap: 1rem; }
  .gap-4 { gap: 1.5rem; }

  /* Tamaños de texto (Bootstrap 5) */
  .fs-5 { font-size: 1.25rem; }
  .fs-4 { font-size: 1.5rem; }
  .fs-6 { font-size: 1rem; }
  .display-6 { font-size: 2rem; line-height: 1.2; }

  /* Evita anchos fijos en pantallas pequeñas */
  .br-scroll-x { overflow-x: auto; -webkit-overflow-scrolling: touch; }
  img, table { max-width: 100%; }

  /* ---------- Cabeceras de tarjeta uniformes en todo el módulo ---------- */
  .card > .card-header.bg-primary,
  .card-primary:not(.card-outline) > .card-header {
    background: linear-gradient(135deg, #0A6AA0 0%, var(--br-primary) 55%, var(--br-primary-dark) 100%) !important;
    border-bottom: 0;
    color: #fff;
  }
  .card > .card-header.bg-primary .card-title,
  .card > .card-header.bg-primary h3,
  .card-primary:not(.card-outline) > .card-header .card-title { color: #fff; font-weight: 700; }
  /* Edición: cabecera clara con borde ámbar (antes amarillo plano) */
  .card > .card-header.bg-warning {
    background: #FFFBEB !important;
    border-bottom: 1px solid #FDE68A;
    box-shadow: inset 4px 0 0 #F59E0B;
    color: #78350F;
  }
  .card > .card-header.bg-warning .card-title { color: #78350F; font-weight: 700; }
  .card-header .btn-tool { color: inherit; opacity: .85; }
  .card-header .btn-tool:hover { opacity: 1; }

  /* ---------- Bienvenida (inicio del módulo) ---------- */
  .br-hero {
    position: relative;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 2rem 2.25rem;
    border-radius: 20px;
    color: #fff;
    overflow: hidden;
    background:
      radial-gradient(circle at 90% 10%, rgba(255, 255, 255, .12) 0, transparent 40%),
      linear-gradient(135deg, #003F63 0%, #005484 55%, #0A6AA0 100%);
    box-shadow: 0 16px 40px rgba(0, 63, 99, .25);
  }
  .br-hero::after {
    content: ""; position: absolute; width: 260px; height: 260px; border-radius: 50%;
    left: -90px; bottom: -150px; background: rgba(255, 255, 255, .06);
  }
  .br-hero__texto { position: relative; z-index: 1; flex: 1 1 auto; min-width: 0; }
  .br-hero__etiqueta {
    display: inline-block; font-size: .78rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
    background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .25);
    padding: .25rem .7rem; border-radius: 999px; margin-bottom: .75rem;
  }
  .br-hero h1 { font-size: 2rem; font-weight: 800; margin: 0 0 .35rem; color: #fff; letter-spacing: -.01em; }
  .br-hero p { color: #D5E7F3; font-size: 1.05rem; margin: 0 0 1.25rem; max-width: 520px; }
  .br-hero__acciones { display: flex; flex-wrap: wrap; gap: .6rem; }
  .br-hero__acciones .btn-light { color: var(--br-primary-dark); font-weight: 700; }
  .br-hero__acciones .btn-outline-light:hover { color: var(--br-primary-dark); }
  .br-hero__figura { position: relative; z-index: 1; flex: 0 0 300px; margin: -1rem -1rem -1rem 0; }
  .br-hero__figura img { width: 100%; height: auto; display: block; }
  @media (max-width: 991.98px) { .br-hero__figura { flex-basis: 220px; } }
  @media (max-width: 767.98px) {
    .br-hero { padding: 1.5rem 1.25rem; }
    .br-hero__figura { display: none; }
    .br-hero h1 { font-size: 1.55rem; }
  }

  /* Títulos de sección y tarjetas de acceso (.br-seccion, .br-modulo): ver assets/conbienes/tema.css */
</style>
