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

  <!-- CSS Personalizado -->
  @stack('css')
</head>

<body class="@yield('body-class', '')">
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

  <!-- Scripts personalizados -->
  @stack('js')

</body>
</html>
