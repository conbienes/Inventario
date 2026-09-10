@extends("theme.$theme.layout")
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">

                {{-- Mensajes flash --}}
                {{-- Mensajes flash unificados con SweetAlert2 --}}
                @if (session('success'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: `<span style="font-size:1.1em; font-weight:600; color:#28a745;">Éxito</span>`,
                                icon: 'success',
                                html: `<p style="font-size:0.95em; color:#333;">{{ session('success') }}</p>`,
                                confirmButtonText: 'Entendido',
                                focusConfirm: true
                            });
                        });
                    </script>
                @endif

                @if (session('error'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: `<span style="font-size:1.1em; font-weight:600; color:#d9534f;">Error</span>`,
                                icon: 'error',
                                html: `<p style="font-size:0.95em; color:#333;">{{ session('error') }}</p>`,
                                confirmButtonText: 'Cerrar',
                                focusConfirm: true
                            });
                        });
                    </script>
                @endif

                @if ($errors->any())
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                title: `<span style="font-size:1.1em; font-weight:600; color:#d9534f;">Errores de validación</span>`,
                                icon: 'error',
                                html: `
                    <ul style="text-align:left; font-size:0.9em; color:#333; max-height:200px; overflow-y:auto; padding-left:1.2rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                `,
                                confirmButtonText: 'Revisar',
                                focusConfirm: true
                            });
                        });
                    </script>
                @endif

                {{-- Warning con detalle (SweetAlert2) --}}
                @if (session('warning'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const data = @json(session('warning'));
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
                                        ${Array.isArray(data.tarjetas) && data.tarjetas.length
                                            ? `                                                                                                                                                                                                                                <details style="margin-top:.5rem;">
                                                                                                                                                                              <summary style="cursor:pointer; font-weight:600; color:#d9534f;">
                                                                                                                                                                                  ${data.tarjetas.length} ${data.tarjetas.length > 1 ? 'tarjetas fallidas' : 'tarjeta fallida'}
                                                                                                                                                                              </summary>
                                                                                                                                                                              <ul style="margin-top:.5rem; max-height:200px; overflow-y:auto; padding-left:1.2rem;">
                                                                                                                                                                                  ${data.tarjetas.map(t => `<li>${t}</li>`).join('')}
                                                                                                                                                                              </ul>
                                                                                                                                                                          </details>
                                                                                                                                                                      `
                                            : `<p style="margin-top:.5rem;">No hubo tarjetas fallidas.</p>`
                                        }
                                    </div>
                                `,
                                confirmButtonText: 'Entendido',
                                focusConfirm: true
                            });
                        });
                    </script>
                @endif

                <div class="card card-primary">


                    <form action="{{ route('BonoRegalo.crearFactura') }}" method="POST" id="invoiceForm">
                        @csrf
                        <div class="card-body">
                            {{-- Datos generales --}}
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
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

                                <div class="col-md-5">
                                    <div class="form-group">
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

                            {{-- Tarjetas Bono Regalo --}}
                            <div class="border-top pt-4">
                                <div class="row align-items-end mb-3">
                                    <div class="col-md-5">
                                        <label for="tarjetaSelect" class="form-label">Seleccionar Tarjeta</label>
                                        <select id="tarjetaSelect" class="form-control"></select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="precioTarjeta" class="form-label">Precio</label>
                                        <input type="text" id="precioTarjeta" class="form-control" placeholder="Precio"
                                            readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" id="btnAgregarTarjeta" class="btn btn-success btn-block">
                                            <i class="fas fa-plus"></i> Agregar
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="tablaTarjetas">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th width="50%">Tarjeta</th>
                                                <th width="25%">Precio</th>
                                                <th width="25%">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="text-right mt-3">
                                    <input type="hidden" id="totalFacturaRaw" name="total_factura" value="0">
                                    <h4 class="font-weight-bold">Total: <span id="totalFactura"
                                            class="text-primary">$0,00</span></h4>
                                </div>
                            </div>

                            {{-- Medios de pago --}}
                            <div class="border-top pt-4">
                                <div class="row align-items-end mb-3">
                                    <div class="col-md-5">
                                        <label for="paymentMethod">Método</label>
                                        <select id="paymentMethod" class="form-control">
                                            <option value="">Seleccione método</option>
                                            @foreach ($paymentMethods as $method)
                                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="paymentAmount">Monto</label>
                                        <input type="number" step="0.01" id="paymentAmount" class="form-control"
                                            placeholder="0.00">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" id="btnAddPayment" class="btn btn-success btn-block">
                                            <i class="fas fa-plus"></i> Agregar pago
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="paymentsTable">
                                        <thead>
                                            <tr>
                                                <th>Método</th>
                                                <th>Monto</th>
                                                <th>Referencia</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="text-right mt-2">
                                    <h5>Total factura: <span id="totalInvoiceDisplay" class="text-primary">$0,00</span>
                                    </h5>
                                    <h5>Pagado: <span id="paidSum" class="text-success">$0,00</span></h5>
                                    <h5>Restante: <span id="remaining" class="text-danger">$0,00</span></h5>
                                    <h5 id="changeRow" style="display:none;">Cambio: <span id="change"
                                            class="text-info">$0,00</span></h5>
                                </div>

                            </div>
                        </div>

                        <div class="card-footer bg-light text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Guardar
                            </button>
                            <a href="{{ route('BonoRegalo.inicio') }}" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-arrow-left mr-1"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- jQuery + Select2 (igual que tu Inventario) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            let precio = $(this).find(':selected').data('precio') || '';
            $('#precioTarjeta').val(precio);
        });
    </script>


    <script>
        // Utilidades
        const moneyFmt = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            maximumFractionDigits: 0
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
            $('#precioTarjeta').val(data.precio || '');
        });

        // Limpiar el precio si se borra la selección
        $('#tarjetaSelect').on('select2:clear', function() {
            $('#precioTarjeta').val('');
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
                return markup; // Permite HTML en los resultados
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
                let precio = $(this).find(':selected').data('precio') || '';
                $('#precioTarjeta').val(precio);
            });

            // Agregar tarjeta a la tabla
            $('#btnAgregarTarjeta').click(function() {
                let select = $('#tarjetaSelect');
                let id = select.val();
                let nombre = select.find(':selected').text();
                let precio = parseFloat($('#precioTarjeta').val()) || 0;

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
                <td>${precio.toFixed(2)}<input type="hidden" name="precios[]" value="${precio}"></td>
                <td><button type="button" class="btn btn-danger btn-sm btnEliminar">Eliminar</button></td>
            </tr>
        `);

                recalcularTotal();
                select.val('').trigger('change');
                $('#precioTarjeta').val('');
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
                    maximumFractionDigits: 0
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
                maximumFractionDigits: 0
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
                const amount = num($('#paymentAmount').val());

                if (!methodId) return alert('Seleccione método');
                if (amount <= 0) return alert('Ingrese un monto válido');

                // Índice para esta nueva fila (0..n-1)
                const idx = $('#paymentsTable tbody tr').length;

                const row = `
        <tr>
          <td>
            ${methodTxt}
            <input type="hidden" name="payments[${idx}][method_id]" value="${methodId}">
          </td>
          <td>
            ${money(amount)}
            <input type="hidden" name="payments[${idx}][amount]" value="${amount.toFixed(2)}">
          </td>
          <td>
            <input type="text" name="payments[${idx}][reference]" class="form-control form-control-sm" placeholder="Referencia (Opc.)">
          </td>
          <td>
            <button type="button" class="btn btn-danger btn-sm btnRemovePayment">Eliminar</button>
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

                // (Opcional) Si hay sobrepago y el último método NO es efectivo (id=1), bloquear o avisar
                const change = totalPaid - totalFactura;
                if (change > 0) {
                    // if (String(lastMethod) !== '1') {
                    //   e.preventDefault();
                    //   return Swal.fire({
                    //     icon: 'info',
                    //     title: 'Sobrepago detectado',
                    //     text: 'El cambio sólo se permite con pagos en efectivo.'
                    //   });
                    // }
                    // O solo avisar:
                    // Swal.fire({ icon: 'info', title: 'Cambio a devolver', text: `Debes devolver ${money(change)}.` });
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('invoiceForm');

            form.addEventListener('submit', function() {
                Swal.fire({
                    title: 'Guardando factura...',
                    html: '<div style="font-size:0.95em; color:#666;">Por favor espera un momento</div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            });
        });
    </script>

    <style>
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            padding: 0.375rem 0.75rem !important;
            line-height: 1.5 !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            background-color: #fff !important;
            /* Fondo blanco */
            color: #495057 !important;
            /* Texto gris oscuro */
            padding-right: 0 !important;
            /* Sin espacio para flecha */
        }

        /* Texto dentro del select */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5 !important;
            padding-left: 0 !important;
            color: #495057 !important;
            background-color: transparent !important;
        }

        /* Quitar la flecha */
        .select2-selection__arrow {
            display: none !important;
        }

        /* Opciones del dropdown */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #f8f9fa !important;
            color: #ffffff !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e9ecef !important;
            color: #ffffff !important;
        }

        /* Caja del dropdown */
        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endsection
