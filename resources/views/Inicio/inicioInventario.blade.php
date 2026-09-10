@extends("theme.$theme.layout")
@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" data-auto-dismiss="3000">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <ul>
                <li>{{ session('success') }}</li>
            </ul>
        </div>
    @endif

    @if (session('success1'))
        <div class="alert alert-warning alert-dismissible" data-auto-dismiss="3000">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <ul>
                <li>{{ session('success1') }}</li>
            </ul>
        </div>
    @endif
    <div class="container">
        <!-- Sección de Solicitudes Enviadas -->
        <div class="section">
            <h2>Solicitudes Enviadas</h2>
            <div class="info-box-container">
                <div class="info-box">
                    <span class="info-box-icon">
                        <img src="{{ asset("assets/lte/dist/img/1.png") }}" alt="Crear Solicitud"/>
                    </span>
                    <a href="{{ route('Crear_SolicProduc') }}" class="info-box-link">
                        <div class="info-box-content">
                            <span class="info-box-text">Crear Solicitud</span>
                        </div>
                    </a>
                </div>

                <div class="info-box">
                    <span class="info-box-icon">
                        <img src="{{ asset("assets/$theme/dist/img/2.png") }}" alt="Solicitudes Pendientes"/>
                    </span>
                    <a href="{{ route('Pendientes') }}" class="info-box-link">
                        <div class="info-box-content">
                            <span class="info-box-text">Solicitudes Pendientes</span>
                            {{ count($solicitudPedidos) }}
                        </div>
                    </a>
                </div>

                <div class="info-box">
                    <span class="info-box-icon">
                        <img src="{{ asset("assets/$theme/dist/img/3.png") }}" alt="Solicitudes Aprobadas"/>
                    </span>
                    <a href="{{ route('Aprobadas') }}" class="info-box-link">
                        <div class="info-box-content">
                            <span class="info-box-text">Solicitudes Aprobadas</span>
                            {{ count($Aprobadas) }}
                        </div>
                    </a>
                </div>
            </div>
        </div>

         @if (tienePermisoModulo(auth()->id(), 1, [1,2]))
            <!-- Sección de Solicitudes Recibidas -->
            <div class="section">
                <h2>Solicitudes Recibidas</h2>
                <div class="info-box-container">
                    <div class="info-box">
                        <span class="info-box-icon">
                            <img src="{{ asset("assets/$theme/dist/img/4.png") }}" alt="Solicitudes Recibidas"/>
                        </span>
                        <a href="{{ route('Solicitudes') }}" class="info-box-link">
                            <div class="info-box-content">
                                <span class="info-box-text">Solicitudes Recibidas</span>
                                {{ count($pendientes) }}
                            </div>
                        </a>
                    </div>


                    <div class="info-box">
                        <span class="info-box-icon">
                            <img src="{{ asset("assets/$theme/dist/img/5.png") }}" alt="Solicitudes Aprobadas"/>
                        </span>
                        <a href="{{ route('TotalAprobadas') }}" class="info-box-link">
                            <div class="info-box-content">
                                <span class="info-box-text">Solicitudes Aprobadas</span>
                                {{ count($TotalAprobadas) }}
                            </div>
                        </a>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon">
                            <img src="{{ asset("assets/$theme/dist/img/6.png") }}" alt="Inventario"/>
                        </span>
                        <a href="{{ route('Inventario') }}" class="info-box-link">
                            <div class="info-box-content">
                                <span class="info-box-text">Inventario</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sección de Entrada Almacén -->
            <div class="section">
                <h2>Entrada Almacen</h2>
                <div class="info-box-container">
                    <div class="info-box">
                        <span class="info-box-icon">
                            <img src="{{ asset("assets/$theme/dist/img/7.png") }}" alt="Arqueo"/>
                        </span>
                        <a href="{{ route('In_Arqueo') }}" class="info-box-link">
                            <div class="info-box-content">
                                <span class="info-box-text">Arqueo</span>
                            </div>
                        </a>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon">
                            <img src="{{ asset("assets/$theme/dist/img/8.png") }}" alt="Entradas Almacén"/>
                        </span>
                        <a href="{{ route('EntAlmacen') }}" class="info-box-link">
                            <div class="info-box-content">
                                <span class="info-box-text">Entradas Almacen</span>
                            </div>
                        </a>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon">
                            <img src="{{ asset("assets/$theme/dist/img/9.png") }}" alt="Alertas Stock"/>
                        </span>
                        <a href="{{ route('InicioAlertaStock') }}" class="info-box-link">
                            <div class="info-box-content">
                                <span class="info-box-text">Alertas Stock</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

<style>
    .container {
        max-width: 100px;
        margin: auto;
        padding: 20px;
    }

    .section {
        margin-bottom: 40px;
    }

    .section h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #000;
    }

    .info-box-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }

    .info-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        width: 250px;
        transition: transform 0.3s ease;
    }

    .info-box:hover {
        transform: translateY(-5px);
    }

    .info-box-icon img {
        width: 60px;
        height: 60px;
        object-fit: contain;
    }

    .info-box-link {
        text-decoration: none;
        color: #000;
        width: 100%;
    }

    .info-box-content {
        width: 100%;
    }

    .info-box-text {
        font-size: 16px;
        margin-top: 10px;
        display: block;
    }

    /* Responsividad para tablets */
    @media (max-width: 768px) {
        .info-box {
            width: calc(50% - 20px);
        }
    }

    /* Responsividad para teléfonos */
    @media (max-width: 480px) {
        .info-box {
            width: 100%;
            padding: 15px;
        }

        .info-box-icon img {
            width: 50px;
            height: 50px;
        }

        .info-box-text {
            font-size: 14px;
        }
        .container {
        max-width: 1000px;
    }
    }
</style>
