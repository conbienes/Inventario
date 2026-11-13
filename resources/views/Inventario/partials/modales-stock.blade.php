{{-- Ventanas para editar el stock desde una tabla (Inventario, Alertas de stock).
     En cada fila, los botones llevan:
       class="js-ajustar" o "js-limites"
       data-id, data-producto, data-codigo, data-actual, data-min, data-max
     Al guardar se vuelve a esta misma página (con sus filtros). --}}

@if ($errors->has('Cantidad') || $errors->has('Descripcion') || $errors->has('CantidadMin') || $errors->has('CantidadMax'))
    <div class="alert alert-danger" role="alert">
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> No se guardó el cambio:</strong>
        <ul class="mb-0 pl-3 mt-1">
            @foreach (['Cantidad', 'Descripcion', 'CantidadMin', 'CantidadMax'] as $campo)
                @foreach ($errors->get($campo) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endif

{{-- Ajustar cantidad --}}
<div class="modal fade" id="modalAjustar" tabindex="-1" role="dialog" aria-labelledby="tituloAjustar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" method="POST" action="{{ route('actualizar_EditInven') }}" id="formAjustar">
            @csrf
            <input type="hidden" name="Id">
            <input type="hidden" name="volver" value="{{ request()->fullUrl() }}">
            <input type="hidden" name="Cantidad" id="ajDiferencia">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="tituloAjustar">Ajustar stock</h5>
                    <small class="text-muted js-nombre"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body">
                <div class="inv-resumen mb-3">
                    <div class="inv-dato"><small>En el sistema</small><strong class="js-actual">0</strong></div>
                    <div class="inv-dato"><small>Mínimo</small><strong class="js-min">0</strong></div>
                    <div class="inv-dato"><small>Máximo</small><strong class="js-max">0</strong></div>
                </div>

                <div class="form-group">
                    <label for="ajNueva">Cantidad real <span class="inv-campo-obligatorio">*</span></label>
                    <input type="number" id="ajNueva" class="form-control form-control-lg" step="any" min="0" required autocomplete="off">
                    <div class="d-flex flex-wrap mt-2" style="gap:.4rem">
                        <button type="button" class="btn btn-sm btn-outline-secondary js-atajo" data-hasta="min">Llenar hasta el mínimo</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary js-atajo" data-hasta="max">Llenar hasta el máximo</button>
                    </div>
                </div>

                <div class="p-3 mb-3 text-center" style="background:#F8FAFC;border-radius:12px">
                    <small class="text-muted d-block">Se registrará un movimiento de</small>
                    <strong class="js-diferencia" style="font-size:1.4rem">0</strong>
                </div>

                <div class="form-group mb-0">
                    <label for="ajMotivo">Motivo <span class="inv-campo-obligatorio">*</span></label>
                    <textarea id="ajMotivo" name="Descripcion" class="form-control" rows="2" maxlength="255" required
                              placeholder="Ej.: conteo físico, reposición, producto dañado"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" id="ajGuardar" disabled><i class="fas fa-save mr-1"></i> Guardar ajuste</button>
            </div>
        </form>
    </div>
</div>

{{-- Mínimo y máximo --}}
<div class="modal fade" id="modalLimites" tabindex="-1" role="dialog" aria-labelledby="tituloLimites" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" method="POST" action="{{ route('ActualizarLimites') }}" id="formLimites">
            @csrf
            <input type="hidden" name="Id">
            <input type="hidden" name="volver" value="{{ request()->fullUrl() }}">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="tituloLimites">Mínimo y máximo</h5>
                    <small class="text-muted js-nombre"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body">
                <p class="inv-texto-2 mb-3">Cuando la cantidad baje del <strong>mínimo</strong>, el producto aparece en Alertas de stock. El <strong>máximo</strong> es lo ideal para reponer.</p>
                <div class="row">
                    <div class="form-group col-6">
                        <label for="limMin">Mínimo <span class="inv-campo-obligatorio">*</span></label>
                        <input type="number" id="limMin" name="CantidadMin" class="form-control form-control-lg" step="any" min="0" required>
                    </div>
                    <div class="form-group col-6">
                        <label for="limMax">Máximo <span class="inv-campo-obligatorio">*</span></label>
                        <input type="number" id="limMax" name="CantidadMax" class="form-control form-control-lg" step="any" min="0" required>
                    </div>
                </div>
                <small class="text-muted">Cantidad actual: <strong class="js-actual">0</strong></small>
                <div class="text-danger small mt-2 d-none" id="limError">El máximo no puede ser menor que el mínimo.</div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    $(function () {
        if ($.fn.tooltip) $('[data-toggle="tooltip"]').tooltip();
        var fmt = function (n) { return Number(n).toLocaleString('es-CO', { maximumFractionDigits: 2 }); };
        var datos = {};

        function cargar($modal, btn) {
            datos = {
                id: btn.dataset.id,
                producto: btn.dataset.producto + (btn.dataset.codigo ? ' · Código ' + btn.dataset.codigo : ''),
                actual: parseFloat(btn.dataset.actual) || 0,
                min: parseFloat(btn.dataset.min) || 0,
                max: parseFloat(btn.dataset.max) || 0
            };
            $modal.find('input[name="Id"]').val(datos.id);
            $modal.find('.js-nombre').text(datos.producto);
            $modal.find('.js-actual').text(fmt(datos.actual));
            $modal.find('.js-min').text(fmt(datos.min));
            $modal.find('.js-max').text(fmt(datos.max));
        }

        // --- Ajustar cantidad ---
        function calcular() {
            var valor = $('#ajNueva').val();
            var dif = valor === '' ? 0 : (parseFloat(valor) - datos.actual);
            $('#ajDiferencia').val(dif);
            var $d = $('.js-diferencia');
            $d.text((dif > 0 ? '+' : '') + fmt(dif) + (dif > 0 ? ' (entrada)' : dif < 0 ? ' (salida)' : ''));
            $d.css('color', dif > 0 ? '#15803D' : dif < 0 ? '#B91C1C' : '#1F2937');
            $('#ajGuardar').prop('disabled', valor === '' || dif === 0);
        }

        $(document).on('click', '.js-ajustar', function () {
            cargar($('#modalAjustar'), this);
            $('#ajNueva').val('');
            $('#ajMotivo').val('');
            calcular();
            $('#modalAjustar').modal('show');
        });
        $('#modalAjustar').on('shown.bs.modal', function () { $('#ajNueva').trigger('focus'); });
        $('#ajNueva').on('input', calcular);
        $('.js-atajo').on('click', function () {
            var hasta = this.dataset.hasta === 'max' ? datos.max : datos.min;
            $('#ajNueva').val(Math.max(hasta, datos.actual));
            if (!$('#ajMotivo').val()) $('#ajMotivo').val('REPOSICIÓN HASTA EL ' + (this.dataset.hasta === 'max' ? 'MÁXIMO' : 'MÍNIMO'));
            calcular();
        });

        // --- Mínimo y máximo ---
        $(document).on('click', '.js-limites', function () {
            cargar($('#modalLimites'), this);
            $('#limMin').val(datos.min);
            $('#limMax').val(datos.max);
            $('#limError').addClass('d-none');
            $('#modalLimites').modal('show');
        });
        $('#formLimites').on('submit', function (e) {
            if (parseFloat($('#limMax').val()) < parseFloat($('#limMin').val())) {
                e.preventDefault();
                $('#limError').removeClass('d-none');
            }
        });

        // Evitar doble envío
        $('#formAjustar, #formLimites').on('submit', function (e) {
            if (e.isDefaultPrevented()) return;
            $(this).find('button[type="submit"]').prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm mr-1"></span> Guardando…');
        });
    });
</script>
@endpush
