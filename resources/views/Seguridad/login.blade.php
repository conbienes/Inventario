<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · CONBIENES</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primario: #005484;
            --primario-oscuro: #003F63;
            --primario-claro: #E0F2FE;
            --acento: #F59E0B;
            --peligro: #B91C1C;
            --texto: #1F2937;
            --texto-2: #4B5563;
            --borde: #CBD5E1;
            --fondo-campo: #F8FAFC;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "Source Sans 3", "Segoe UI", system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            color: var(--texto);
            background:
                radial-gradient(circle at 12% 18%, rgba(125, 211, 252, .18) 0, transparent 38%),
                radial-gradient(circle at 88% 85%, rgba(245, 158, 11, .14) 0, transparent 35%),
                linear-gradient(135deg, #00304D 0%, #005484 60%, #0A6AA0 100%);
        }

        .login {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            width: 100%;
            max-width: 980px;
            min-height: 580px;
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0, 20, 40, .45);
        }

        /* ---------- Panel ilustración ---------- */
        .panel {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 36px 40px;
            color: #fff;
            background: linear-gradient(160deg, #003F63 0%, #005484 55%, #0A6AA0 100%);
            overflow: hidden;
        }

        .panel::before,
        .panel::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .06);
        }
        .panel::before { width: 360px; height: 360px; top: -140px; right: -120px; }
        .panel::after { width: 240px; height: 240px; bottom: -110px; left: -80px; }

        .panel__marca { position: relative; z-index: 1; }
        .panel__marca img { height: 54px; width: auto; }

        .panel__figura {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 0;
        }

        .panel__figura img {
            width: 100%;
            max-width: 400px;
            height: auto;
            transition: opacity .3s ease, transform .3s ease;
        }
        .panel__figura img.cambiando { opacity: 0; transform: translateY(8px) scale(.97); }

        .panel__texto { position: relative; z-index: 1; min-height: 92px; }
        .panel__etiqueta {
            display: inline-block;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .22);
            padding: 4px 10px;
            border-radius: 999px;
            margin-bottom: 10px;
        }
        .panel__texto h2 { font-size: 1.6rem; font-weight: 800; line-height: 1.2; margin-bottom: 6px; }
        .panel__texto p { color: #CFE3F0; font-size: 1rem; line-height: 1.45; max-width: 380px; }

        /* ---------- Formulario ---------- */
        .formulario {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 48px 36px;
        }

        .formulario__titulo { margin-bottom: 28px; }
        .formulario__titulo h1 { font-size: 1.85rem; font-weight: 800; letter-spacing: -.01em; }
        .formulario__titulo p { color: var(--texto-2); margin-top: 4px; }

        .campo { margin-bottom: 18px; }
        .campo label {
            display: block;
            font-weight: 600;
            font-size: .95rem;
            margin-bottom: 6px;
        }

        .control { position: relative; }
        .control svg.icono {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #64748B;
            pointer-events: none;
        }

        .control input,
        .control select {
            width: 100%;
            height: 48px;
            padding: 0 44px 0 44px;
            font: inherit;
            font-size: 1rem;
            color: var(--texto);
            background: var(--fondo-campo);
            border: 1.5px solid var(--borde);
            border-radius: 12px;
            transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
        }

        .control select { appearance: none; -webkit-appearance: none; cursor: pointer; }

        .control input:focus,
        .control select:focus {
            outline: none;
            background: #fff;
            border-color: var(--primario);
            box-shadow: 0 0 0 4px rgba(0, 84, 132, .15);
        }

        .control .flecha {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--primario);
            pointer-events: none;
        }

        .ver-clave {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border: 0;
            background: transparent;
            border-radius: 8px;
            color: #64748B;
            cursor: pointer;
        }
        .ver-clave:hover { background: #EEF2F6; color: var(--primario); }
        .ver-clave:focus-visible { outline: 2px solid var(--primario); }
        .ver-clave svg { width: 20px; height: 20px; }

        /* Selector de módulo tipo tarjeta */
        .modulos { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .modulo-op { position: relative; }
        .modulo-op input { position: absolute; opacity: 0; pointer-events: none; }
        .modulo-op label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            padding: 12px;
            border: 1.5px solid var(--borde);
            border-radius: 12px;
            background: var(--fondo-campo);
            cursor: pointer;
            transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
        }
        .modulo-op label:hover { border-color: #93C5FD; }
        .modulo-op .ico {
            flex: none;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: #E2E8F0;
            color: var(--texto-2);
            transition: background-color .15s ease, color .15s ease;
        }
        .modulo-op .ico svg { width: 20px; height: 20px; }
        .modulo-op strong { display: block; font-size: .98rem; }
        .modulo-op small { display: block; font-size: .8rem; color: var(--texto-2); font-weight: 400; }
        .modulo-op input:checked + label {
            border-color: var(--primario);
            background: var(--primario-claro);
            box-shadow: 0 0 0 3px rgba(0, 84, 132, .12);
        }
        .modulo-op input:checked + label .ico { background: var(--primario); color: #fff; }
        .modulo-op input:focus-visible + label { outline: 2px solid var(--primario); outline-offset: 2px; }

        .errores {
            display: flex;
            gap: 10px;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #7F1D1D;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: .93rem;
        }
        .errores svg { flex: none; width: 20px; height: 20px; color: var(--peligro); margin-top: 1px; }
        .errores p + p { margin-top: 2px; }

        .boton {
            width: 100%;
            height: 50px;
            margin-top: 6px;
            border: 0;
            border-radius: 12px;
            font: inherit;
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #0A6AA0 0%, var(--primario) 60%, var(--primario-oscuro) 100%);
            box-shadow: 0 8px 20px rgba(0, 84, 132, .3);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform .1s ease, box-shadow .15s ease, filter .15s ease;
        }
        .boton:hover { filter: brightness(1.08); box-shadow: 0 10px 24px rgba(0, 84, 132, .38); }
        .boton:active { transform: translateY(1px); }
        .boton:focus-visible { outline: 3px solid #7DD3FC; outline-offset: 2px; }
        .boton svg { width: 20px; height: 20px; }
        .boton[disabled] { opacity: .75; cursor: wait; }

        .pie {
            margin-top: 26px;
            text-align: center;
            font-size: .82rem;
            color: #64748B;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 860px) {
            .login { grid-template-columns: 1fr; max-width: 460px; min-height: 0; }
            .panel { padding: 22px 24px; flex-direction: row; align-items: center; gap: 14px; }
            .panel__figura, .panel__texto p, .panel__etiqueta { display: none; }
            .panel__texto { min-height: 0; }
            .panel__texto h2 { font-size: 1.1rem; margin: 0; }
            .panel__marca img { height: 40px; }
            .formulario { padding: 28px 24px 24px; }
        }

        @media (max-width: 380px) {
            .modulos { grid-template-columns: 1fr; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>

<body>
    @php
        $moduloPrevio = (string) old('modulo', '');
    @endphp

    <main class="login">
        {{-- Panel ilustrado: cambia según el módulo elegido --}}
        <section class="panel" aria-hidden="true">
            <div class="panel__marca">
                <img src="{{ asset('assets/lte/dist/img/logo.jpg') }}" alt="CONBIENES">
            </div>

            <div class="panel__figura">
                <img id="figura" src="{{ asset('assets/conbienes/img/login-bienvenida.svg') }}" alt="">
            </div>

            <div class="panel__texto">
                <span class="panel__etiqueta" id="etiqueta">CONBIENES</span>
                <h2 id="titulo">Gestión simple de tu operación</h2>
                <p id="descripcion">Elige el módulo con el que vas a trabajar hoy e ingresa con tu documento.</p>
            </div>
        </section>

        {{-- Formulario --}}
        <section class="formulario">
            <div class="formulario__titulo">
                <h1>Bienvenido</h1>
                <p>Ingresa tus datos para continuar.</p>
            </div>

            @if ($errors->any())
                <div class="errores" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login_post') }}" id="formLogin" novalidate>
                @csrf

                <div class="campo">
                    <label for="cedula">Documento</label>
                    <div class="control">
                        <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.6-1.4 1.8-2 3-2s2.4.6 3 2M15 10h3M15 14h3"/></svg>
                        <input type="text" id="cedula" name="cedula" value="{{ old('cedula') }}" inputmode="numeric"
                            autocomplete="username" placeholder="Número de documento" required autofocus>
                    </div>
                </div>

                <div class="campo">
                    <label for="password">Contraseña</label>
                    <div class="control">
                        <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        <input type="password" id="password" name="password" autocomplete="current-password"
                            placeholder="Tu contraseña" required>
                        <button type="button" class="ver-clave" id="verClave" aria-label="Mostrar contraseña">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="campo">
                    <label id="lblModulo">Módulo</label>
                    <div class="modulos" role="radiogroup" aria-labelledby="lblModulo">
                        <div class="modulo-op">
                            <input type="radio" name="modulo" id="mod1" value="1" required
                                data-image="{{ asset('assets/conbienes/img/login-inventario.svg') }}"
                                data-etiqueta="Módulo Inventario"
                                data-titulo="Tu almacén, en orden"
                                data-descripcion="Solicitudes, entradas, existencias y alertas de stock en un solo lugar."
                                @checked($moduloPrevio === '1')>
                            <label for="mod1">
                                <span class="ico">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/></svg>
                                </span>
                                <span><strong>Inventario</strong><small>Almacén y stock</small></span>
                            </label>
                        </div>

                        <div class="modulo-op">
                            <input type="radio" name="modulo" id="mod3" value="3"
                                data-image="{{ asset('assets/conbienes/img/login-bono.svg') }}"
                                data-etiqueta="Módulo Bono Regalo"
                                data-titulo="Regala experiencias"
                                data-descripcion="Vende y controla tarjetas de bono regalo, clientes y recibos de caja."
                                @checked($moduloPrevio === '3')>
                            <label for="mod3">
                                <span class="ico">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5C11 3 12 8 12 8s1-5 4.5-5a2.5 2.5 0 0 1 0 5"/></svg>
                                </span>
                                <span><strong>Bono Regalo</strong><small>Tarjetas y ventas</small></span>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="boton" id="btnIngresar">
                    Ingresar
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>

            <p class="pie">© {{ date('Y') }} CONBIENES · Todos los derechos reservados</p>
        </section>
    </main>

    <script>
        (function () {
            const figura = document.getElementById('figura');
            const etiqueta = document.getElementById('etiqueta');
            const titulo = document.getElementById('titulo');
            const descripcion = document.getElementById('descripcion');
            const radios = document.querySelectorAll('input[name="modulo"]');

            function mostrar(radio) {
                if (!radio) return;
                figura.classList.add('cambiando');
                setTimeout(function () {
                    figura.src = radio.dataset.image;
                    etiqueta.textContent = radio.dataset.etiqueta;
                    titulo.textContent = radio.dataset.titulo;
                    descripcion.textContent = radio.dataset.descripcion;
                    figura.classList.remove('cambiando');
                }, 220);
            }

            radios.forEach(function (r) { r.addEventListener('change', function () { mostrar(r); }); });
            mostrar(document.querySelector('input[name="modulo"]:checked'));

            // Mostrar / ocultar contraseña
            const clave = document.getElementById('password');
            const ver = document.getElementById('verClave');
            ver.addEventListener('click', function () {
                const oculta = clave.type === 'password';
                clave.type = oculta ? 'text' : 'password';
                ver.setAttribute('aria-label', oculta ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });

            // Validación simple: avisar si falta el módulo y evitar doble envío
            const form = document.getElementById('formLogin');
            form.addEventListener('submit', function (e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    form.reportValidity();
                    return;
                }
                const btn = document.getElementById('btnIngresar');
                btn.disabled = true;
                btn.firstChild.textContent = 'Ingresando… ';
            });
        })();
    </script>
</body>

</html>
