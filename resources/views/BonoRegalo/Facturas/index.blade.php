@extends("theme.$theme.layout")

@php
    // Notificaciones "simples" unificadas (éxito, error, validación) para evitar
    // repetir el mismo bloque HTML de SweetAlert2 tres veces.
    $flashNotices = [];

    if (session('success')) {
        $flashNotices[] = [
            'icon' => 'success',
            'color' => '#28a745',
            'title' => 'Éxito',
            'html' => '<p style="font-size:0.95em;color:#333;">' . e(session('success')) . '</p>',
            'confirmButtonText' => 'Entendido',
        ];
    }

    if (session('error')) {
        $flashNotices[] = [
            'icon' => 'error',
            'color' => '#d9534f',
            'title' => 'Error',
            'html' => '<p style="font-size:0.95em;color:#333;">' . e(session('error')) . '</p>',
            'confirmButtonText' => 'Cerrar',
        ];
    }

    if ($errors->any()) {
        $errorItems = collect($errors->all())->map(fn ($error) => '<li>' . e($error) . '</li>')->implode('');
        $flashNotices[] = [
            'icon' => 'error',
            'color' => '#d9534f',
            'title' => 'Errores de validación',
            'html' => '<ul style="text-align:left;font-size:0.9em;color:#333;max-height:200px;overflow-y:auto;padding-left:1.2rem;">' . $errorItems . '</ul>',
            'confirmButtonText' => 'Revisar',
        ];
    }
@endphp

@section('content')
    <div class="pos-canvas">
    <div class="container-fluid">

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
            <div>
                <h3 class="mb-1 font-weight-bold text-dark">
                    <i class="fas fa-gift text-primary mr-2"></i>Nueva Factura &mdash; Bono Regalo
                </h3>
                <p class="text-muted mb-0">Registra la venta de tarjetas de bono regalo y sus medios de pago.</p>
            </div>
            <a href="{{ route('BonoRegalo.inicio') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>

        <form action="{{ route('BonoRegalo.crearFactura') }}" method="POST" id="invoiceForm">
            @csrf
            <div class="row">

                {{-- ==================== COLUMNA IZQUIERDA: ÁREA DE TRABAJO ==================== --}}
                <div class="col-lg-8">

                    {{-- Datos generales --}}
                    <div class="card pos-card shadow-sm rounded-lg mb-4">
                        <div class="card-body p-3 p-md-4">
                            <h6 class="text-uppercase text-muted font-weight-bold small mb-3">
                                <i class="fas fa-info-circle mr-1"></i> Datos generales
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="fecha" class="font-weight-bold">Fecha <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="fecha" id="fecha"
                                            class="form-control @error('fecha') is-invalid @enderror"
                                            value="{{ old('fecha', date('Y-m-d')) }}" required>
                                        @error('fecha')
                                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-8 mt-3 mt-md-0">
                                    <div class="form-group mb-0">
                                        <label for="cliente" class="font-weight-bold">Cliente <span
                                                class="text-danger">*</span></label>
                                        <select id="cliente" name="cliente" class="form-control cliente"
                                            style="width:100%" required
                                            data-placeholder="Buscar por cédula o nombre"></select>
                                        @error('cliente')
                                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Agregar tarjetas --}}
                    <div class="card pos-card shadow-sm rounded-lg mb-4">
                        <div class="card-body p-3 p-md-4">
                            <h6 class="text-uppercase text-muted font-weight-bold small mb-3">
                                <i class="fas fa-credit-card mr-1"></i> Agregar Tarjeta
                            </h6>
                            <div class="row align-items-end">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <label for="tarjetaSelect" class="small text-muted mb-1">Seleccionar
                                        Tarjeta</label>
                                    <select id="tarjetaSelect" class="form-control"></select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label for="precioTarjeta" class="small text-muted mb-1">Precio</label>
                                    <input type="text" id="precioTarjeta" class="form-control bg-light"
                                        placeholder="Precio" readonly>
                                    <input type="hidden" id="precioTarjetaRaw" value="">
                                </div>
                                <div class="col-6 col-md-3">
                                    <button type="button" id="btnAgregarTarjeta"
                                        class="btn btn-success btn-block">
                                        <i class="fas fa-plus mr-1"></i> Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Agregar pagos --}}
                    <div class="card pos-card shadow-sm rounded-lg">
                        <div class="card-body p-3 p-md-4">
                            <h6 class="text-uppercase text-muted font-weight-bold small mb-3">
                                <i class="fas fa-money-check-alt mr-1"></i> Agregar Pago
                            </h6>
                            <div class="row align-items-end">
                                <div class="col-12 col-md-5 mb-3 mb-md-0">
                                    <label for="paymentMethod" class="small text-muted mb-1">Método</label>
                                    <select id="paymentMethod" class="form-control">
                                        <option value="">Seleccione método</option>
                                        @foreach ($paymentMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label for="paymentAmount" class="small text-muted mb-1">Monto</label>
                                    <input type="text" inputmode="numeric" id="paymentAmount" class="form-control"
                                        placeholder="0" autocomplete="off">
                                </div>
                                <div class="col-6 col-md-4">
                                    <button type="button" id="btnAddPayment" class="btn btn-success btn-block">
                                        <i class="fas fa-plus mr-1"></i> Agregar pago
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ==================== COLUMNA DERECHA: TICKET / RESUMEN ==================== --}}
                <div class="col-lg-4">
                    <div class="card ticket-panel pos-card shadow rounded-lg">
                        <div class="card-body p-3 p-md-4">
                            <h6 class="text-uppercase text-muted font-weight-bold small mb-3">
                                <i class="fas fa-receipt mr-1"></i> Ticket de Compra
                            </h6>

                            {{-- Tarjetas agregadas --}}
                            <span class="small text-muted text-uppercase font-weight-bold d-block mb-1">Tarjetas</span>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless align-middle mb-0" id="tablaTarjetas">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th class="border-0">Tarjeta</th>
                                            <th class="border-0 text-right">Precio</th>
                                            <th class="border-0 text-center" width="1%">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 pb-3 border-bottom">
                                <input type="hidden" id="totalFacturaRaw" name="total_factura" value="0">
                                <span class="text-muted small">Subtotal tarjetas</span>
                                <span id="totalFactura" class="font-weight-bold text-primary">$0,00</span>
                            </div>

                            {{-- Pagos agregados --}}
                            <span class="small text-muted text-uppercase font-weight-bold d-block mt-3 mb-1">Pagos</span>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless align-middle mb-0" id="paymentsTable">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th class="border-0">Método</th>
                                            <th class="border-0 text-right">Monto</th>
                                            <th class="border-0">Ref.</th>
                                            <th class="border-0 text-center" width="1%">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- Totales --}}
                            <div class="summary-ticket rounded p-3 mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Total factura</span>
                                    <span id="totalInvoiceDisplay" class="font-weight-bold text-dark">$0,00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Pagado</span>
                                    <span id="paidSum" class="font-weight-bold text-success">$0,00</span>
                                </div>
                                <hr class="summary-divider my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Restante</span>
                                    <span id="remaining" class="font-weight-bold text-danger h5 mb-0">$0,00</span>
                                </div>
                                <div id="changeRow" class="d-flex justify-content-between align-items-center mt-2"
                                    style="display:none;">
                                    <span class="text-muted">Cambio</span>
                                    <span id="change" class="font-weight-bold text-info">$0,00</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-block rounded-pill shadow-sm mt-4">
                                <i class="fas fa-save mr-2"></i> Guardar Factura
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
    </div>
@endsection

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Fondo de "mesa de trabajo" para que las tarjetas blancas resalten */
        .pos-canvas {
            background-color: #eef1f6;
            padding: 1.5rem 0 2.5rem;
            min-height: calc(100vh - 3.5rem);
        }

        .pos-card {
            border: 1px solid #e3e6f0;
        }

        .select2-container .select2-selection--single {
            position: relative !important;
            height: calc(2.25rem + 2px) !important;
            padding: 0.375rem 0.75rem !important;
            line-height: 1.5 !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            background-color: #fff !important;
            color: #495057 !important;
            padding-right: 1.75rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            color: #495057 !important;
            background-color: transparent !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .select2-selection__arrow {
            display: none !important;
        }

        /* El botón "x" de limpiar selección se queda dentro de la caja, no se desborda */
        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute !important;
            top: 50% !important;
            right: 0.5rem !important;
            margin: 0 !important;
            transform: translateY(-50%);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #f8f9fa !important;
            color: #ffffff !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e9ecef !important;
            color: #ffffff !important;
        }

        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Ticket / carrito lateral */
        .ticket-panel {
            background-color: #fcfcfd;
            border-color: #d8dde6 !important;
        }

        @media (min-width: 992px) {
            .ticket-panel {
                position: sticky;
                top: 1rem;
            }
        }

        /* Panel resumen de pago tipo "ticket" */
        .summary-ticket {
            background: linear-gradient(135deg, #f8f9fc, #eef1f8);
            border: 1px solid #e3e6f0;
        }

        .summary-divider {
            border: none;
            border-top: 1px dashed #ced4da;
        }

        #tablaTarjetas tbody tr td,
        #paymentsTable tbody tr td {
            vertical-align: middle;
        }

        /* Tablas del ticket: layout fijo para que nunca se desborden ni exijan scroll horizontal */
        #tablaTarjetas,
        #paymentsTable {
            table-layout: fixed;
            width: 100%;
            font-size: .82rem;
        }

        #tablaTarjetas td,
        #tablaTarjetas th {
            overflow-wrap: break-word;
            word-break: break-all;
        }

        #tablaTarjetas th:nth-child(1),
        #tablaTarjetas td:nth-child(1) {
            width: 54%;
        }

        #tablaTarjetas th:nth-child(2),
        #tablaTarjetas td:nth-child(2) {
            width: 31%;
        }

        #tablaTarjetas th:nth-child(3),
        #tablaTarjetas td:nth-child(3) {
            width: 15%;
        }

        #paymentsTable th:nth-child(1),
        #paymentsTable td:nth-child(1) {
            width: 27%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #paymentsTable th:nth-child(2),
        #paymentsTable td:nth-child(2) {
            width: 26%;
        }

        #paymentsTable th:nth-child(3),
        #paymentsTable td:nth-child(3) {
            width: 32%;
        }

        #paymentsTable th:nth-child(4),
        #paymentsTable td:nth-child(4) {
            width: 15%;
        }

        #paymentsTable input[type="text"] {
            width: 100%;
            min-width: 0;
            padding: 0.25rem 0.4rem;
            font-size: .78rem;
        }
    </style>
@endpush

@push('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (count($flashNotices))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const flashNotices = @json($flashNotices);

                function showFlash(index) {
                    if (index >= flashNotices.length) return;
                    const notice = flashNotices[index];
                    Swal.fire({
                        title: `<span style="font-size:1.1em; font-weight:600; color:${notice.color};">${notice.title}</span>`,
                        icon: notice.icon,
                        html: notice.html,
                        confirmButtonText: notice.confirmButtonText,
                        focusConfirm: true
                    }).then(() => showFlash(index + 1));
                }
                showFlash(0);
            });
        </script>
    @endif

    @if (session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const data = @json(session('warning'));
                const tarjetasHtml = Array.isArray(data.tarjetas) && data.tarjetas.length ? `
                    <details style="margin-top:.5rem;">
                        <summary style="cursor:pointer; font-weight:600; color:#d9534f;">
                            ${data.tarjetas.length} ${data.tarjetas.length > 1 ? 'tarjetas fallidas' : 'tarjeta fallida'}
                        </summary>
                        <ul style="margin-top:.5rem; max-height:200px; overflow-y:auto; padding-left:1.2rem;">
                            ${data.tarjetas.map(t => `<li>${t}</li>`).join('')}
                        </ul>
                    </details>
                ` : `<p style="margin-top:.5rem;">No hubo tarjetas fallidas.</p>`;

                Swal.fire({
                    title: `<span style="font-size:1.1em; font-weight:600; color:#444;">${data.mensaje}</span>`,
                    icon: 'warning',
                    width: 600,
                    html: `
                        <div style="text-align:left; font-size:0.95em; color:#333;">
                            <p><strong>Número de Recibo de Caja:</strong>
                                <span style="color:#28a745;">${data.factura ?? ''}</span>
                            </p>
                            <p><strong>Total exitoso:</strong>
                                <span style="color:#007bff;">$
                                    ${new Intl.NumberFormat('es-CO').format(data.totalExitoso || 0)}
                                </span>
                            </p>
                            ${tarjetasHtml}
                        </div>
                    `,
                    confirmButtonText: 'Entendido',
                    focusConfirm: true
                });
            });
        </script>
    @endif

    <script>
        // Activar Select2 para buscar en el select
        $('#tarjetaSelect').select2({
            theme: "classic", // o "bootstrap4" si quieres más integración
            placeholder: "Buscar tarjeta por número",
            allowClear: true,
            width: 'resolve' // respeta el ancho del select
        });
        // Mostrar el precio al seleccionar
        $('#tarjetaSelect').on('change', function() {
            let precio = $(this).find(':selected').data('precio') || 0;
            $('#precioTarjetaRaw').val(precio);
            $('#precioTarjeta').val(precio ? numFmt.format(precio) : '');
        });
    </script>

    <script>
        // Utilidades
        const moneyFmt = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        const numFmt = new Intl.NumberFormat('es-CO');

        function toNumberFromText(text) {
            if (!text) return 0;
            return parseFloat(String(text).replace(/[^0-9.-]+/g, '')) || 0;
        }

        function money(n) {
            return moneyFmt.format(n || 0);
        }
        $('#tarjetaSelect').select2({
            theme: "classic",
            placeholder: 'Seleccione una tarjeta',
            allowClear: true,
            language: {
                noResults: function() {
                    return "No se encontraron tarjetas";
                },
                searching: function() {
                    return "Buscando...";
                }
            },
            ajax: {
                url: '{{ route('buscarTarjetasBR') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.text,
                                precio: item.precio
                            };
                        })
                    };
                }
            }
        });

        // Mostrar el precio cuando se seleccione una tarjeta
        $('#tarjetaSelect').on('select2:select', function(e) {
            var data = e.params.data;
            var precio = data.precio || 0;
            $('#precioTarjetaRaw').val(precio);
            $('#precioTarjeta').val(precio ? numFmt.format(precio) : '');
        });

        // Limpiar el precio si se borra la selección
        $('#tarjetaSelect').on('select2:clear', function() {
            $('#precioTarjeta').val('');
            $('#precioTarjetaRaw').val('');
        });


        $('.cliente').select2({
            theme: "classic",
            language: {
                noResults: function() {
                    return `
                <div class="text-center p-2">
                    Cliente no encontrado <br>
                    <button type="button" id="btnCrearCliente" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-plus"></i> Crear Cliente
                    </button>
                </div>
            `;
                },
                searching: function() {
                    return "Buscando...";
                }
            },
            escapeMarkup: function(markup) {
                return markup; // Permite el HTML del botón "Crear Cliente" (mensaje noResults)
            },
            // Los nombres de clientes se pintan como TEXTO (no HTML): evita ejecutar código guardado en un nombre
            templateResult: function(item) {
                return item.loading ? item.text : $('<span>').text(item.text);
            },
            templateSelection: function(item) {
                return $('<span>').text(item.text);
            },
            ajax: {
                url: '/Ajax/buscarClientesBR',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
            },
            minimumInputLength: 1,
            placeholder: 'Seleccione un cliente'
        });

        // Evento para abrir el modal o redirigir a crear cliente
        $(document).on('click', '#btnCrearCliente', function() {
            window.location.href = "{{ route('BonoRegalo.IndexCliente') }}";
        });

        // 🔹 Asegurar que el valor se mantenga en el <select>
        $('.cliente').on('select2:select', function(e) {
            var data = e.params.data;
            $(this).empty().append(
                $('<option>', {
                    value: data.id,
                    text: data.text,
                    selected: true
                })
            );
        });
    </script>

    <script>
        $(document).ready(function() {
            // Mostrar precio al seleccionar tarjeta
            $('#tarjetaSelect').change(function() {
                let precio = $(this).find(':selected').data('precio') || 0;
                $('#precioTarjetaRaw').val(precio);
                $('#precioTarjeta').val(precio ? numFmt.format(precio) : '');
            });

            // Agregar tarjeta a la tabla
            $('#btnAgregarTarjeta').click(function() {
                let select = $('#tarjetaSelect');
                let id = select.val();
                let nombre = select.find(':selected').text();
                let precio = parseFloat($('#precioTarjetaRaw').val()) || 0;

                // Evitar tarjetas duplicadas
                const yaExiste = $('#tablaTarjetas input[name="tarjetas[]"][value="' + id + '"]').length >
                    0;
                if (yaExiste) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tarjeta ya agregada',
                        text: 'Esta tarjeta ya está en la lista.'
                    });
                    return;
                }

                if (!id) {
                    alert('Seleccione una tarjeta');
                    return;
                }


                // Agregar fila a la tabla
                $('#tablaTarjetas tbody').append(`
            <tr>
                <td>${nombre}<input type="hidden" name="tarjetas[]" value="${id}"></td>
                <td class="text-right">${money(precio)}<input type="hidden" name="precios[]" value="${precio}"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle btnEliminar" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `);

                recalcularTotal();
                select.val('').trigger('change');
                $('#precioTarjeta').val('');
                $('#precioTarjetaRaw').val('');
            });

            // Eliminar fila
            $(document).on('click', '.btnEliminar', function() {
                $(this).closest('tr').remove();
                recalcularTotal();
            });

            // Calcular total
            function recalcularTotal() {
                let total = 0;
                $('#tablaTarjetas tbody tr').each(function() {
                    total += parseFloat($(this).find('input[name="precios[]"]').val()) || 0;
                });

                // 1) Guarda el total REAL en un hidden
                $('#totalFacturaRaw').val(total.toFixed(2));

                // 2) Muestra en pantalla con formato moneda
                const fmt = new Intl.NumberFormat('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                $('#totalFactura').text(fmt.format(total));
                $('#totalInvoiceDisplay').text(fmt.format(total));

                // 3) Recalcula pagos con base en el total ACTUAL
                if (typeof recalcularPagos === 'function') {
                    recalcularPagos();
                }
            }
        });
    </script>

    <script>
        $(function() {
            // === Formateadores ===
            const COP = new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            function money(n) {
                return COP.format(parseFloat(n || 0));
            }

            function num(n) {
                return parseFloat(n || 0);
            } // interno (sin formato)

            function getTotalFactura() {
                return num($('#totalFacturaRaw').val()); // SIEMPRE el total fresco de las tarjetas
            }

            // Convierte "656.262" (con separador de miles) al número real 656262
            function parsePesos(text) {
                return parseInt(String(text || '').replace(/\D/g, ''), 10) || 0;
            }

            // Formatea el monto con separador de miles mientras se escribe
            $('#paymentAmount').on('input', function() {
                const digits = parsePesos(this.value);
                this.value = digits ? numFmt.format(digits) : '';
            });

            // Muestra total inicial por si ya viene cargado
            $('#totalInvoiceDisplay').text(money(getTotalFactura()));

            // ==== Recalcular pagos (muestra restante y cambio) ====
            window.recalcularPagos = function() {
                const totalFactura = getTotalFactura();

                let totalPaid = 0;
                // ⬇⬇⬇ selector actualizado para campos indexados [i]
                $('input[name^="payments["][name$="[amount]"]').each(function() {
                    totalPaid += num($(this).val());
                });

                const remaining = totalFactura - totalPaid;
                const change = totalPaid - totalFactura;

                // Displays con formato local
                $('#paidSum').text(money(totalPaid));
                $('#remaining').text(money(Math.max(remaining, 0)));

                // (Opcional) fila de cambio si la tienes en el HTML
                if ($('#changeRow').length) {
                    if (change > 0) {
                        $('#change').text(money(change));
                        $('#changeRow').show();
                    } else {
                        $('#changeRow').hide();
                    }
                }
            };

            // ==== Agregar pago ====
            $('#btnAddPayment').on('click', function() {
                const methodId = $('#paymentMethod').val(); // 1..6
                const methodTxt = $('#paymentMethod option:selected').text();
                const amount = parsePesos($('#paymentAmount').val());

                if (!methodId) return alert('Seleccione método');
                if (amount <= 0) return alert('Ingrese un monto válido');

                // Pagos exactos: el monto no puede superar el saldo pendiente (no se registra cambio)
                let pagado = 0;
                $('input[name^="payments["][name$="[amount]"]').each(function() {
                    pagado += num($(this).val());
                });
                const pendiente = Math.round((getTotalFactura() - pagado) * 100) / 100;
                if (pendiente <= 0) {
                    return Swal.fire({
                        icon: 'info',
                        title: 'Factura ya pagada',
                        text: 'Los pagos registrados ya cubren el total de la factura.'
                    });
                }
                if (amount > pendiente) {
                    return Swal.fire({
                        icon: 'warning',
                        title: 'Monto mayor al saldo',
                        html: `El pago (${money(amount)}) supera el saldo pendiente (<b>${money(pendiente)}</b>).<br>` +
                            'Registra el valor exacto: si el cliente recibe cambio, descuéntalo.'
                    });
                }

                // Índice para esta nueva fila (0..n-1)
                const idx = $('#paymentsTable tbody tr').length;

                const row = `
        <tr>
          <td>
            ${methodTxt}
            <input type="hidden" name="payments[${idx}][method_id]" value="${methodId}">
          </td>
          <td class="text-right">
            ${money(amount)}
            <input type="hidden" name="payments[${idx}][amount]" value="${amount.toFixed(2)}">
          </td>
          <td>
            <input type="text" name="payments[${idx}][reference]" class="form-control form-control-sm" placeholder="Ref. (Opc.)">
          </td>
          <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle btnRemovePayment" title="Eliminar">
              <i class="fas fa-trash-alt"></i>
            </button>
          </td>
        </tr>`;
                $('#paymentsTable tbody').append(row);

                $('#paymentMethod').val('');
                $('#paymentAmount').val('');
                window.recalcularPagos();
            });

            // Eliminar pago
            $(document).on('click', '.btnRemovePayment', function() {
                $(this).closest('tr').remove();
                window.recalcularPagos();
            });

            // ==== Validación final ====
            $('#invoiceForm').on('submit', function(e) {
                const totalFactura = getTotalFactura();
                let totalPaid = 0,
                    lastMethod = null;

                $('#paymentsTable tbody tr').each(function() {
                    // ⬇⬇⬇ selectores actualizados
                    const amt = num($(this).find('input[name^="payments["][name$="[amount]"]')
                        .val());
                    const mtd = $(this).find('input[name^="payments["][name$="[method_id]"]').val();
                    totalPaid += amt;
                    lastMethod = mtd; // último agregado
                });

                if (totalPaid < totalFactura) {
                    e.preventDefault();
                    return Swal.fire({
                        icon: 'warning',
                        title: 'Pago incompleto',
                        text: 'El total de pagos no cubre el total de la factura.'
                    });
                }

                // Pagos exactos: no se permite sobrepago (p. ej. si se quitó una tarjeta después de registrar el pago)
                if (Math.round(totalPaid * 100) > Math.round(totalFactura * 100)) {
                    e.preventDefault();
                    return Swal.fire({
                        icon: 'warning',
                        title: 'Pagos mayores al total',
                        html: `Los pagos (${money(totalPaid)}) superan el total de la factura (<b>${money(totalFactura)}</b>).<br>` +
                            'Ajusta los pagos al valor exacto.'
                    });
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('invoiceForm');

            form.addEventListener('submit', function(e) {
                // Si otra validación canceló el envío (p. ej. "Pago incompleto"), no tapar su aviso
                // con el modal de "Guardando…", que no se puede cerrar y bloquearía la pantalla.
                // setTimeout: se decide cuando ya corrieron TODOS los manejadores de submit (el orden
                // entre este listener y el de jQuery no está garantizado).
                setTimeout(function() {
                    if (e.defaultPrevented) return;

                    Swal.fire({
                        title: 'Guardando factura...',
                        html: '<div style="font-size:0.95em; color:#666;">Por favor espera un momento</div>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }, 0);
            });
        });
    </script>
@endpush
