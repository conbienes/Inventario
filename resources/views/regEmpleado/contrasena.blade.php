@extends("theme.$theme.layout")

@section('content')

    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cambiar Contraseña</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --primary-color: #4e73df;
                --secondary-color: #6f42c1;
                --success-color: #1cc88a;
                --danger-color: #e74a3b;
                --light-bg: #f8f9fc;
            }

            body {
                background-color: var(--light-bg);
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .password-container {
                max-width: 500px;
                margin: 2rem auto;
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
                border-radius: 0.75rem;
                overflow: hidden;
            }

            .card-header {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                font-weight: 600;
                text-align: center;
                padding: 1.2rem;
            }

            .card-body {
                padding: 2rem;
            }

            .form-control:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            }

            .password-toggle {
                cursor: pointer;
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: #6c757d;
            }

            .password-input-container {
                position: relative;
            }

            .btn-primary {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
                padding: 0.5rem 1.5rem;
                font-weight: 500;
                transition: all 0.3s;
            }

            .btn-primary:hover {
                background-color: var(--secondary-color);
                border-color: var(--secondary-color);
                transform: translateY(-2px);
            }

            .btn-outline-secondary {
                padding: 0.5rem 1.5rem;
                font-weight: 500;
                transition: all 0.3s;
            }

            .btn-outline-secondary:hover {
                transform: translateY(-2px);
            }

            .alert {
                border: none;
                border-radius: 0.5rem;
                padding: 1rem;
            }

            .alert-danger {
                background-color: #f8d7da;
                color: #721c24;
            }

            .password-strength {
                height: 5px;
                margin-top: 5px;
                border-radius: 2.5px;
                background-color: #e9ecef;
            }

            .password-strength-bar {
                height: 100%;
                border-radius: 2.5px;
                width: 0;
                transition: width 0.3s;
            }

            .password-requirements {
                font-size: 0.8rem;
                color: #6c757d;
                margin-top: 0.5rem;
            }

            .action-buttons {
                display: flex;
                justify-content: center;
                gap: 1rem;
                margin-top: 2rem;
            }
        </style>
    </head>

    <body>
        <div class="container my-5">
            <div class="password-container card">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-key me-2"></i>{{ __('Cambiar Contraseña') }}</h3>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible mx-3 mt-3">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Error</h6>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('actualizar_contrasena', ['id' => $empleado->id]) }}"
                    id="passwordForm">
                    @csrf @method('put')
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="password" class="form-label">{{ __('Nueva Contraseña') }}</label>
                            <div class="password-input-container">
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    autocomplete="new-password" required>
                                <span class="password-toggle" id="passwordToggle">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="passwordStrengthBar"></div>
                            </div>
                            <div class="password-requirements">
                                <small>La contraseña debe tener al menos 8 caracteres, incluir una mayúscula, un número y un
                                    carácter especial.</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">{{ __('Confirmar Contraseña') }}</label>
                            <div class="password-input-container">
                                <input id="password_confirmation" type="password" class="form-control"
                                    name="password_confirmation" autocomplete="new-password" required>
                                <span class="password-toggle" id="confirmPasswordToggle">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div id="passwordMatch" class="form-text"></div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('Actualizar') }}
                            </button>

                            @php
                                $modulo = session('modulo_seleccionado');
                                switch ($modulo) {
                                    case 1:
                                        $ruta = route('InicioInventario');
                                        break;
                                    case 2:
                                        $ruta = route('InicioInventario');
                                        break;
                                    case 3:
                                        $ruta = route('BonoRegalo.inicio');
                                        break;
                                    default:
                                        $ruta = route('InicioInventario');
                                        break;
                                }
                            @endphp

                            <a class="btn btn-outline-secondary" href="{{ $ruta }}">
                                <i class="fas fa-times me-2"></i>{{ __('Cancelar') }}
                            </a>

                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle password visibility
                const passwordToggle = document.getElementById('passwordToggle');
                const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
                const passwordField = document.getElementById('password');
                const confirmPasswordField = document.getElementById('password_confirmation');
                const strengthBar = document.getElementById('passwordStrengthBar');
                const passwordMatch = document.getElementById('passwordMatch');

                passwordToggle.addEventListener('click', function() {
                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        passwordToggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    } else {
                        passwordField.type = 'password';
                        passwordToggle.innerHTML = '<i class="fas fa-eye"></i>';
                    }
                });

                confirmPasswordToggle.addEventListener('click', function() {
                    if (confirmPasswordField.type === 'password') {
                        confirmPasswordField.type = 'text';
                        confirmPasswordToggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    } else {
                        confirmPasswordField.type = 'password';
                        confirmPasswordToggle.innerHTML = '<i class="fas fa-eye"></i>';
                    }
                });

                // Password strength indicator
                passwordField.addEventListener('input', function() {
                    const password = passwordField.value;
                    let strength = 0;

                    // Check password length
                    if (password.length >= 8) strength += 25;

                    // Check for uppercase letters
                    if (/[A-Z]/.test(password)) strength += 25;

                    // Check for numbers
                    if (/[0-9]/.test(password)) strength += 25;

                    // Check for special characters
                    if (/[^A-Za-z0-9]/.test(password)) strength += 25;

                    // Update strength bar
                    strengthBar.style.width = strength + '%';

                    // Update color
                    if (strength < 50) {
                        strengthBar.style.backgroundColor = '#e74a3b'; // Red
                    } else if (strength < 100) {
                        strengthBar.style.backgroundColor = '#f6c23e'; // Yellow
                    } else {
                        strengthBar.style.backgroundColor = '#1cc88a'; // Green
                    }
                });

                // Password confirmation check
                confirmPasswordField.addEventListener('input', function() {
                    if (confirmPasswordField.value !== passwordField.value) {
                        passwordMatch.textContent = 'Las contraseñas no coinciden';
                        passwordMatch.style.color = '#e74a3b';
                    } else {
                        passwordMatch.textContent = 'Las contraseñas coinciden';
                        passwordMatch.style.color = '#1cc88a';
                    }
                });

                // Form validation
                document.getElementById('passwordForm').addEventListener('submit', function(e) {
                    if (passwordField.value !== confirmPasswordField.value) {
                        e.preventDefault();
                        alert('Las contraseñas no coinciden. Por favor, verifique.');
                    }
                });
            });
        </script>
    </body>

    </html>
@endsection
