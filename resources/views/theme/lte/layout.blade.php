<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>@yield('title', 'CONBIENES')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Favicon -->
  <link rel="icon" href="{{ asset('favicon.ico') }}">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset("assets/$theme/plugins/fontawesome-free/css/all.min.css") }}">

  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset("assets/$theme/dist/css/adminlte.min.css") }}">

  <!-- Tema visual CONBIENES (capa sobre AdminLTE, aplica a todas las pantallas) -->
  <link rel="stylesheet" href="{{ asset('assets/conbienes/tema.css') }}">

  <!-- CSS Personalizado -->
  @stack('css')

  {{-- Transición suave entre páginas (tema.css): si el navegador no puede completarla (clic rápido en otro
       enlace, pestaña oculta, recarga, descarga) se omite en silencio en lugar de dejar un error en consola. --}}
  <script>
    (function () {
      function silenciar(vt) {
        if (!vt) return;
        ['ready', 'finished', 'updateCallbackDone'].forEach(function (p) {
          if (vt[p] && vt[p].catch) vt[p].catch(function () {});
        });
      }
      // Página que se va
      window.addEventListener('pageswap', function (e) {
        if (!e.viewTransition) return;
        silenciar(e.viewTransition);
        var act = e.activation;
        var destino = act && act.entry && act.entry.url ? act.entry.url : '';
        if (!act || act.navigationType === 'reload' || /exportar|plantilla/i.test(destino) || document.visibilityState !== 'visible') {
          e.viewTransition.skipTransition();
        }
      });
      // Con clics muy rápidos Chrome rechaza una transición que ya no está al alcance de la página
      // ("Page already revealed"). Solo se descarta ese error; cualquier otro se sigue mostrando.
      window.addEventListener('unhandledrejection', function (e) {
        var r = e.reason;
        if (r && r.name === 'InvalidStateError' && /transition was aborted/i.test(r.message || '')) {
          e.preventDefault();
        }
      });
      // Página que llega
      window.addEventListener('pagereveal', function (e) {
        if (!e.viewTransition) return;
        silenciar(e.viewTransition);
        if (document.visibilityState !== 'visible') e.viewTransition.skipTransition();
      });
    })();
  </script>
</head>

<body class="sidebar-mini @yield('body-class', '')">
  {{-- Recuerda si el menú lateral quedó colapsado; se aplica antes de pintar para que no "salte" --}}
  <script>
    try {
      if (window.innerWidth >= 992 && localStorage.getItem('cb.menu') === 'colapsado') {
        document.body.classList.add('sidebar-collapse');
      }
    } catch (e) {}
  </script>
  <!-- Site wrapper -->
  <div class="wrapper">

    <!-- Header/Navbar -->
    @include("theme/$theme/header")

    <!-- Sidebar -->
    @include("theme/$theme/aside")

    <!-- Content Wrapper -->
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">
        @yield('content')
      </section>
    </div>

    <!-- Footer -->
    @include("theme/$theme/footer")

  </div>

  <!-- jQuery -->
  <script src="{{ asset("assets/$theme/plugins/jquery/jquery.min.js") }}"></script>

  <!-- Bootstrap 4 -->
  <script src="{{ asset("assets/$theme/plugins/bootstrap/js/bootstrap.bundle.min.js") }}"></script>

  <!-- AdminLTE App -->
  <script src="{{ asset("assets/$theme/dist/js/adminlte.min.js") }}"></script>

  <!-- Guardar el estado del menú lateral (colapsado / abierto) al usar el botón ☰ -->
  <script>
    (function () {
      function recordado() {
        try { return localStorage.getItem('cb.menu') === 'colapsado'; } catch (e) { return false; }
      }
      function aplicar() {
        if (window.innerWidth >= 992 && recordado()) document.body.classList.add('sidebar-collapse');
      }
      // Solo guarda cuando la persona usa el botón (AdminLTE también abre/cierra solo al cargar)
      $(document).on('click', '[data-widget="pushmenu"]', function () {
        setTimeout(function () {
          if (window.innerWidth < 992) return;
          try {
            localStorage.setItem('cb.menu', document.body.classList.contains('sidebar-collapse') ? 'colapsado' : 'abierto');
          } catch (e) {}
        }, 0);
      });
      // AdminLTE vuelve a abrir el menú al terminar de cargar y al cambiar el tamaño: se reaplica enseguida
      $(window).on('load', function () {
        aplicar();
        $(window).on('resize', aplicar);
      });
    })();
  </script>

  <!-- Aviso "Generando informe… / ¡Listo!" en todas las exportaciones -->
  <script src="{{ asset('assets/conbienes/descargas.js') }}"></script>
  <script src="{{ asset('assets/conbienes/tabla-rapida.js') }}"></script>

  <!-- Scripts personalizados -->
  @stack('js')

</body>
</html>
