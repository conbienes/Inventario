@extends("theme.$theme.layout")

@section('content')

    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Panel de Control - Bono Regalo</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            :root {
                --primary-color: #4e73df;
                --secondary-color: #1cc88a;
                --accent-color: #36b9cc;
                --dark-color: #5a5c69;
                --light-bg: #f8f9fc;
            }

            body {
                background-color: var(--light-bg);
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .card-hover {
                transition: all 0.3s ease-in-out;
                border-radius: 12px;
                border: none;
                overflow: hidden;
            }

            .card-hover:hover {
                transform: translateY(-6px);
                box-shadow: 0px 12px 20px rgba(0, 0, 0, 0.15);
            }

            .stat-card {
                border-radius: 10px;
                border: none;
                transition: transform 0.3s;
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            }

            .stat-card:hover {
                transform: translateY(-5px);
            }

            .stat-icon {
                opacity: 0.9;
            }

            .module-icon {
                transition: transform 0.3s;
            }

            .card-module:hover .module-icon {
                transform: scale(1.1);
            }

            .refresh-btn {
                transition: all 0.5s;
                cursor: pointer;
            }

            .refresh-btn:hover {
                transform: rotate(180deg);
            }

            .section-title {
                position: relative;
                padding-bottom: 15px;
                margin-bottom: 25px;
            }

            .section-title:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 80px;
                height: 3px;
                background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
                border-radius: 3px;
            }

            .chart-container {
                position: relative;
                height: 300px;
            }

            .loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(255, 255, 255, 0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10;
                border-radius: 8px;
            }

            .pulse {
                animation: pulse 1.5s infinite;
            }

            @keyframes pulse {
                0% {
                    opacity: 1;
                }

                50% {
                    opacity: 0.5;
                }

                100% {
                    opacity: 1;
                }
            }

            .last-sale-card {
                background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
                color: white;
                border-radius: 10px;
            }
        </style>
    </head>

    <body>
        <div class="container py-4">

            <!-- Módulos de Bono Regalo -->
            <section class="mb-5">
                <h2 class="text-center mb-4 fw-bold text-primary section-title">🎁 Módulos Bono Regalo</h2>
                <div class="row g-4 justify-content-center">
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <a href="#" class="card card-module shadow-sm text-decoration-none card-hover">
                            <div class="card-body text-center py-4">
                                <div class="module-icon mb-3">
                                    <img src="https://cdn-icons-png.flaticon.com/512/1077/1077012.png" class="img-fluid"
                                        style="width: 70px;">
                                </div>
                                <h5 class="text-dark fw-semibold mb-2">Clientes</h5>
                                <p class="text-muted small mb-0">Gestión de clientes</p>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <a href="#" class="card card-module shadow-sm text-decoration-none card-hover">
                            <div class="card-body text-center py-4">
                                <div class="module-icon mb-3">
                                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135706.png" class="img-fluid"
                                        style="width: 70px;">
                                </div>
                                <h5 class="text-dark fw-semibold mb-2">Ventas Bono</h5>
                                <p class="text-muted small mb-0">Registro de ventas</p>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <a href="#" class="card card-module shadow-sm text-decoration-none card-hover">
                            <div class="card-body text-center py-4">
                                <div class="module-icon mb-3">
                                    <img src="https://cdn-icons-png.flaticon.com/512/3594/3594435.png" class="img-fluid"
                                        style="width: 70px;">
                                </div>
                                <h5 class="text-dark fw-semibold mb-2">Informes</h5>
                                <p class="text-muted small mb-0">Reportes y estadísticas</p>
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Tarjetas Bono Regalo -->
            <section class="mb-4">
                <h2 class="text-center mb-4 fw-bold text-success section-title">💳 Tarjetas Bono Regalo</h2>
                <div class="row g-4 justify-content-center">
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <a href="#" class="card card-module shadow-sm text-decoration-none card-hover">
                            <div class="card-body text-center py-4">
                                <div class="module-icon mb-3">
                                    <img src="https://cdn-icons-png.flaticon.com/512/3594/3594445.png" class="img-fluid"
                                        style="width: 70px;">
                                </div>
                                <h5 class="text-dark fw-semibold mb-2">Crear Tarjeta</h5>
                                <p class="text-muted small mb-0">Generar nueva tarjeta</p>
                            </div>
                        </a>
                    </div>
                </div>
            </section>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                // Elementos DOM
                const elTotal = document.getElementById("totalHoy");
                const elCount = document.getElementById("countHoy");
                const elAvg = document.getElementById("avgHoy");
                const elUltima = document.getElementById("ultimaVentaTexto");
                const lastUpdateEl = document.getElementById("lastUpdate");
                const refreshBtn = document.getElementById("refreshBtn");
                const chartLoading = document.getElementById("chartLoading");
                const ctx = document.getElementById('chartVentasHoy').getContext('2d');

                // Elementos de progreso
                const totalProgress = document.getElementById("totalProgress");
                const countProgress = document.getElementById("countProgress");
                const avgProgress = document.getElementById("avgProgress");

                // Variables para almacenar máximos (para las barras de progreso)
                let maxTotal = 0;
                let maxCount = 0;
                let maxAvg = 0;

                // URL de la API (usando la ruta que proporcionaste)
                const apiUrl = '/BonoRegalo/ventas/hoy';

                // Formateador de moneda
                const fmtCOP = v => new Intl.NumberFormat('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    maximumFractionDigits: 0
                }).format(Number(v || 0));

                // Formateador de fecha
                const formatTime = date => date.toLocaleTimeString('es-CO', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                let chart; // instancia Chart.js

                // Inicializar con valores cero
                initializeStats();

                // Cargar datos inmediatamente
                cargar();

                // Configurar evento de actualización manual
                refreshBtn.addEventListener("click", () => {
                    cargar();
                });

                // Auto-refresh cada 60s
                setInterval(cargar, 60000);

                function initializeStats() {
                    elTotal.textContent = fmtCOP(0);
                    elCount.textContent = "0";
                    elAvg.textContent = fmtCOP(0);
                    elUltima.textContent = "Cargando...";
                    lastUpdateEl.textContent = "Actualizando...";

                    // Inicializar barras de progreso
                    totalProgress.style.width = "0%";
                    countProgress.style.width = "0%";
                    avgProgress.style.width = "0%";
                }

                async function cargar() {
                    try {
                        // Mostrar estado de carga
                        lastUpdateEl.textContent = "Actualizando...";
                        chartLoading.style.display = 'flex';

                        const res = await fetch(apiUrl, {
                            headers: {
                                'Cache-Control': 'no-cache',
                                'Pragma': 'no-cache'
                            }
                        });

                        if (!res.ok) {
                            throw new Error(`Error ${res.status}: ${res.statusText}`);
                        }

                        const data = await res.json();

                        // Actualizar UI con los datos
                        updateUI(data);

                        // Actualizar marca de tiempo
                        lastUpdateEl.textContent = `Actualizado: ${formatTime(new Date())}`;

                    } catch (error) {
                        console.error("Error cargando datos:", error);
                        showError();
                    } finally {
                        chartLoading.style.display = 'none';
                    }
                }

                function updateUI(data) {
                    // KPIs
                    const total = Number(data.total || 0);
                    const count = Number(data.count || 0);
                    const avg = Number(data.avg || 0);

                    // Actualizar máximos si es necesario
                    maxTotal = Math.max(maxTotal, total);
                    maxCount = Math.max(maxCount, count);
                    maxAvg = Math.max(maxAvg, avg);

                    // Actualizar valores
                    elTotal.textContent = fmtCOP(total);
                    elCount.textContent = count;
                    elAvg.textContent = fmtCOP(avg);

                    // Actualizar barras de progreso
                    totalProgress.style.width = maxTotal ? `${(total / maxTotal) * 100}%` : "0%";
                    countProgress.style.width = maxCount ? `${(count / maxCount) * 100}%` : "0%";
                    avgProgress.style.width = maxAvg ? `${(avg / maxAvg) * 100}%` : "0%";

                    // Última venta
                    if (Array.isArray(data.ultimas) && data.ultimas.length) {
                        const u = data.ultimas[0];
                        const hora = u.hora || new Date(u.created_at).toLocaleTimeString('es-CO', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        elUltima.textContent = `${fmtCOP(u.valor_total)} a las ${hora}`;
                    } else {
                        elUltima.textContent = "No hay ventas registradas hoy";
                    }

                    // Series para el gráfico
                    const labels = Array.isArray(data.series) ? data.series.map(s => s.hora) : [];
                    const valores = Array.isArray(data.series) ? data.series.map(s => Number(s.total)) : [];

                    // Si no hay datos, muestra una serie plana en 0
                    const safeLabels = labels.length ? labels : ["Sin datos"];
                    const safeValores = valores.length ? valores : [0];

                    // Crear o actualizar gráfico
                    updateChart(safeLabels, safeValores);
                }

                function updateChart(labels, valores) {
                    const cfg = {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Ventas por hora',
                                data: valores,
                                borderColor: '#4e73df',
                                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: '#4e73df',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointHoverRadius: 6,
                                borderWidth: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ` ${fmtCOP(ctx.parsed.y)}`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    title: {
                                        display: true,
                                        text: "Hora del día",
                                        color: '#858796',
                                        font: {
                                            weight: 'bold'
                                        }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    },
                                    title: {
                                        display: true,
                                        text: "Monto en COP",
                                        color: '#858796',
                                        font: {
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        callback: (val) => new Intl.NumberFormat('es-CO', {
                                            maximumFractionDigits: 0
                                        }).format(val)
                                    }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            animations: {
                                tension: {
                                    duration: 1000,
                                    easing: 'linear'
                                }
                            }
                        }
                    };

                    if (chart) {
                        chart.data.labels = cfg.data.labels;
                        chart.data.datasets[0].data = cfg.data.datasets[0].data;
                        chart.update();
                    } else {
                        chart = new Chart(ctx, cfg);
                    }
                }

                function showError() {
                    elTotal.textContent = "Error";
                    elCount.textContent = "—";
                    elAvg.textContent = "Error";
                    elUltima.textContent = "No fue posible cargar los datos";
                    lastUpdateEl.textContent = "Error al actualizar";

                    // Reiniciar barras de progreso
                    totalProgress.style.width = "0%";
                    countProgress.style.width = "0%";
                    avgProgress.style.width = "0%";
                }
            });
        </script>
    </body>

    </html>
@endsection
