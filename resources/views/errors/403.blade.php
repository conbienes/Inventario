{{-- resources/views/errors/403.blade.php --}}
@extends("theme.$theme.layout")

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="text-center mb-5">
                    <div class="error-icon mb-4">
                        <div class="icon-container position-relative">
                            <div class="circle-bg"></div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 24 24"
                                fill="none" stroke="#dc3545" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-lock position-relative">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                    </div>
                    <h1 class="display-3 fw-bold text-danger mb-2">403</h1>
                    <h2 class="h2 text-dark mb-3">Acceso denegado</h2>

                </div>

                <div class="card shadow-lg border-0 rounded-3 overflow-hidden mb-5">
                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center">
                            {{-- Lista de sugerencias --}}
                            <div class="col-md-8">

                                <ul class="list-unstyled mb-4">
                                    <li class="mb-3 p-3 bg-light rounded-2">
                                        <i class="fas fa-arrow-left text-primary me-2 fa-lg"></i>
                                        <span class="fw-medium">Volver a la página anterior</span>
                                    </li>
                                    <li class="mb-3 p-3 bg-light rounded-2">
                                        <i class="fas fa-home text-primary me-2 fa-lg"></i>
                                        <span class="fw-medium">Ir al inicio del módulo</span>
                                    </li>
                                    <li class="p-3 bg-light rounded-2">
                                        <i class="fas fa-user-cog text-primary me-2 fa-lg"></i>
                                        <span class="fw-medium">Contactar al administrador si necesitas acceso</span>
                                    </li>
                                </ul>
                            </div>

                            {{-- Icono decorativo --}}
                            <div class="col-md-4 text-center mt-4 mt-md-0">
                                <div class="bg-primary rounded-circle p-4 d-inline-block shadow">
                                    <i class="fas fa-key fa-3x text-white"></i>
                                </div>
                            </div>
                        </div>

                        @php
                            $modulo = session('modulo_seleccionado');
                            switch ($modulo) {
                                case 1:
                                    $ruta = route('InicioInventario');
                                    $moduloNombre = 'Inventario';
                                    break;
                                case 2:
                                    $ruta = route('InicioInventario');
                                    $moduloNombre = 'Inventario';
                                    break;
                                case 3:
                                    $ruta = route('BonoRegalo.inicio');
                                    $moduloNombre = 'Bono Regalo';
                                    break;
                                default:
                                    $ruta = url('/');
                                    $moduloNombre = 'Inicio';
                                    break;
                            }
                        @endphp

                        {{-- Botones de acción --}}
                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center mt-4">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-lg px-4 py-3 flex-fill">
                                <i class="fas fa-arrow-left me-2"></i> Volver atrás
                            </a>
                            <a href="{{ $ruta }}" class="btn btn-primary btn-lg px-4 py-3 flex-fill">
                                <i class="fas fa-home me-2"></i> Ir a {{ $moduloNombre }}
                            </a>
                            <a href="mailto:administrador@empresa.com"
                                class="btn btn-outline-danger btn-lg px-4 py-3 flex-fill">
                                <i class="fas fa-envelope me-2"></i> Solicitar acceso
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .error-icon {
            animation: pulse 2s infinite ease-in-out;
        }

        .icon-container {
            display: inline-block;
            position: relative;
        }

        .circle-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 160px;
            height: 160px;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 50%;
            z-index: 0;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .card {
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        .rounded-3 {
            border-radius: 1rem !important;
        }

        .btn {
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            font-weight: 500;
            border-width: 2px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .list-unstyled li {
            transition: all 0.3s ease;
        }

        .list-unstyled li:hover {
            background-color: #e9ecef !important;
            transform: translateX(5px);
        }

        .display-3 {
            font-size: 5rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .display-3 {
                font-size: 3.5rem;
            }

            .btn-lg {
                width: 100%;
                margin-bottom: 1rem;
            }

            .d-flex.flex-md-row {
                flex-direction: column;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            .error-message {
                margin-left: 1rem;
                margin-right: 1rem;
            }

            .icon-container .circle-bg {
                width: 120px;
                height: 120px;
            }

            .icon-container svg {
                width: 100px;
                height: 100px;
            }
        }
    </style>
@endsection
