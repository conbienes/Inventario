<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CONBIENES</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c5282;
            --primary-hover: #1a365d;
            --secondary-color: #f7fafc;
            --error-color: #e53e3e;
            --text-color: #2d3748;
            --light-gray: #edf2f7;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #2b6cb0 0%, #4299e1 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--text-color);
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 840px;
            /* Ancho total de ambos paneles */
        }

        .image-panel {
            flex: 1;
            background: white;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
            /* Para el efecto de transición */
        }

        .module-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        /* Efecto hover opcional para la imagen */
        .module-image:hover {
            transform: scale(1.05);
        }

        .login-container {
            flex: 1;
            max-width: 420px;
            background: white;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .login-container:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .login-header {
            background: var(--primary-color);
            padding: 25px;
            text-align: center;
        }

        .logo {
            height: 80px;
            width: auto;
            margin-bottom: 15px;
        }

        .login-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 500;
        }

        .login-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius);
            background-color: var(--secondary-color);
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.3);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .error-message {
            background-color: #fff5f5;
            border-left: 4px solid var(--error-color);
            padding: 12px;
            margin-bottom: 1.5rem;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
        }

        .error-message p {
            color: var(--error-color);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .error-message p:last-child {
            margin-bottom: 0;
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper::after {
            content: "▼";
            font-size: 0.8rem;
            color: var(--primary-color);
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        select.form-control {
            appearance: none;
            padding-right: 35px;
        }


        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                max-width: 420px;
            }

            .image-panel {
                display: none;
                /* Ocultar panel de imagen en móviles */
            }

            .login-container {
                border-radius: var(--border-radius);
            }
        }

        @media (max-width: 480px) {
            .login-body {
                padding: 20px;
            }

            .login-header {
                padding: 20px;
            }

            .logo {
                height: 60px;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <!-- Panel de imagen dinámica -->
        <div class="image-panel">
            <img id="module-preview" src="{{ asset('assets/lte/dist/img/logo.jpg') }}" alt="Vista previa del módulo"
                class="module-image">
        </div>

        <!-- Panel de formulario -->
        <div class="login-container">
            <div class="login-header">
                <img src="{{ asset('assets/lte/dist/img/logo.jpg') }}" alt="Logo CONBIENES" class="logo">
                <h1 class="login-title">Ingreso al Sistema</h1>
            </div>

            <div class="login-body">
                <form method="POST" action="{{ route('login_post') }}">
                    @csrf

                    <div class="form-group">
                        <label for="cedula" class="form-label">Documento</label>
                        <input type="text" id="cedula" name="cedula" class="form-control" required
                            autocomplete="off" autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="modulo" class="form-label">Módulo</label>
                        <div class="select-wrapper">
                            <select name="modulo" id="modulo" class="form-control" required
                                onchange="changeModulePreview()">
                                <option value="">Seleccione un módulo</option>
                                <option value="1"
                                    data-image="{{ asset('assets/lte/dist/img/logo-inventario.png') }}">Inventario
                                </option>
                                <!--  <option value="2"
                                    data-image="{{ asset('assets/lte/dist/img/modulo-restaurante.png') }}">Restaurante
                                </option>  -->
                                <option value="3" data-image="{{ asset('assets/lte/dist/img/logo-bono.png') }}">
                                    Bono Regalo</option>
                            </select>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="error-message">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="btn">Ingresar</button>
                </form>
            </div>
        </div>
    </div>


    <script>
        function changeModulePreview() {
            const select = document.getElementById('modulo');
            const preview = document.getElementById('module-preview');
            const selectedOption = select.options[select.selectedIndex];

            // Aplicar fade out antes de cambiar la imagen
            preview.style.opacity = 0;
            preview.style.transform = 'scale(0.95)';

            setTimeout(() => {
                if (selectedOption.value !== "") {
                    preview.src = selectedOption.getAttribute('data-image');
                    preview.alt = "Vista previa: " + selectedOption.text;
                } else {
                    preview.src = "{{ asset('assets/lte/dist/img/modulo-default.png') }}";
                    preview.alt = "Seleccione un módulo";
                }

                // Aplicar fade in después de cambiar la imagen
                preview.style.opacity = 1;
                preview.style.transform = 'scale(1)';
            }, 300);
        }
    </script>
</body>

</html>
