@extends("theme.$theme.layout")

@section('content')
    @include('regEmpleado.partials.estilos')
    @php
        $rutaVolver = session('modulo_seleccionado') == 3 ? route('BonoRegalo.inicio') : route('InicioInventario');
    @endphp

    <div class="emp-page container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title emp-titulo">
                            <i class="fas fa-key mr-2"></i>Cambiar contraseña
                        </h3>
                        <div class="emp-ayuda mt-1">Cambie su propia contraseña de acceso.</div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger mx-3 mt-3 mb-0" role="alert">
                            <h6 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>No se pudo cambiar la contraseña</h6>
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('actualizar_contrasena', ['id' => $empleado->id]) }}" id="formContrasena">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label for="current_password" class="font-weight-bold">Contraseña actual</label>
                                <input id="current_password" type="password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    name="current_password" autocomplete="current-password" required>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="font-weight-bold">Nueva contraseña</label>
                                <div class="input-group">
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        autocomplete="new-password" required aria-describedby="ayudaPassword">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary js-mostrar" type="button"
                                            data-destino="password" aria-label="Mostrar u ocultar la contraseña">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="emp-fuerza" aria-hidden="true">
                                    <div class="emp-fuerza-barra" id="fuerzaBarra"></div>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small id="ayudaPassword" class="emp-ayuda d-block mt-2">
                                    Mínimo 8 caracteres, con una mayúscula, un número y un carácter especial.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="font-weight-bold">Confirmar nueva contraseña</label>
                                <div class="input-group">
                                    <input id="password_confirmation" type="password" class="form-control"
                                        name="password_confirmation" autocomplete="new-password" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary js-mostrar" type="button"
                                            data-destino="password_confirmation" aria-label="Mostrar u ocultar la confirmación">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <small id="avisoCoincidencia" class="d-block mt-2" aria-live="polite"></small>
                            </div>
                        </div>

                        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center">
                            <a class="btn btn-outline-secondary mb-2" href="{{ $rutaVolver }}">
                                <i class="fas fa-arrow-left mr-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-emp-primario mb-2">
                                <i class="fas fa-save mr-1"></i>Actualizar contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('js')
<script>
    (function () {
        var password = document.getElementById('password');
        var confirmacion = document.getElementById('password_confirmation');
        var barra = document.getElementById('fuerzaBarra');
        var aviso = document.getElementById('avisoCoincidencia');
        var form = document.getElementById('formContrasena');

        // Mostrar / ocultar contraseña
        document.querySelectorAll('.js-mostrar').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var campo = document.getElementById(boton.getAttribute('data-destino'));
                var ver = campo.type === 'password';
                campo.type = ver ? 'text' : 'password';
                boton.querySelector('i').className = ver ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        });

        // Fuerza: longitud, mayúscula, número y carácter especial (25% cada criterio)
        function fuerza(valor) {
            var puntos = 0;
            if (valor.length >= 8) puntos += 25;
            if (/[A-Z]/.test(valor)) puntos += 25;
            if (/[0-9]/.test(valor)) puntos += 25;
            if (/[^A-Za-z0-9]/.test(valor)) puntos += 25;
            return puntos;
        }

        function actualizarFuerza() {
            var puntos = fuerza(password.value);
            barra.style.width = puntos + '%';
            barra.classList.remove('is-debil', 'is-media', 'is-fuerte');
            if (puntos === 0) return;
            if (puntos < 50) barra.classList.add('is-debil');
            else if (puntos < 100) barra.classList.add('is-media');
            else barra.classList.add('is-fuerte');
        }

        function actualizarCoincidencia() {
            if (confirmacion.value === '') {
                aviso.textContent = '';
                aviso.className = 'd-block mt-2';
            } else if (confirmacion.value !== password.value) {
                aviso.textContent = 'Las contraseñas no coinciden.';
                aviso.className = 'd-block mt-2 emp-txt-error';
            } else {
                aviso.textContent = 'Las contraseñas coinciden.';
                aviso.className = 'd-block mt-2 emp-txt-ok';
            }
        }

        password.addEventListener('input', function () { actualizarFuerza(); actualizarCoincidencia(); });
        confirmacion.addEventListener('input', actualizarCoincidencia);

        form.addEventListener('submit', function (e) {
            if (password.value !== confirmacion.value) {
                e.preventDefault();
                actualizarCoincidencia();
                confirmacion.focus();
            }
        });
    })();
</script>
    @endpush
@endsection
