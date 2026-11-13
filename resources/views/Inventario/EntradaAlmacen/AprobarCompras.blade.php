@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Aprobar productos recibidos</h1>
        <p class="inv-subtitulo">Indique las cantidades recibidas de cada producto y el estado de la entrega.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('SolicPendFacturas', ['id' => Crypt::encrypt($div->id)]) }}"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
</div>

@include('Inventario.partials.pasos', ['pasos' => ['Revisar lo recibido', 'Calificar proveedor', 'Notificar al personal'], 'actual' => 1])

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('AprobarItems') }}">
    @csrf
    <div class="inv-tarjeta mb-3">
        <div class="inv-seccion"><i class="fas fa-truck-loading"></i> ¿Cómo llegó la entrega? <span class="inv-campo-obligatorio">*</span></div>
        <div class="inv-opciones" role="radiogroup" aria-label="Estado de la entrega">
            <div class="inv-opcion">
                <input type="radio" name="Estado" id="estado2" value="2" required>
                <label for="estado2">
                    <span class="inv-opcion__ico" style="background:#DCFCE7;color:#15803D"><i class="fas fa-check-double"></i></span>
                    <span><strong>Llegó completo</strong><small>Se recibió todo lo solicitado. No hace falta escribir cantidades.</small></span>
                </label>
            </div>
            <div class="inv-opcion">
                <input type="radio" name="Estado" id="estado1" value="1">
                <label for="estado1">
                    <span class="inv-opcion__ico" style="background:#FEF3C7;color:#B45309"><i class="fas fa-hourglass-half"></i></span>
                    <span><strong>Llegó una parte</strong><small>El pedido sigue abierto para recibir lo que falta.</small></span>
                </label>
            </div>
            <div class="inv-opcion">
                <input type="radio" name="Estado" id="estado3" value="3">
                <label for="estado3">
                    <span class="inv-opcion__ico" style="background:#E0F2FE;color:#005484"><i class="fas fa-lock"></i></span>
                    <span><strong>Parcial y cerrar</strong><small>Llegó una parte y no se espera más. Se cierra con lo recibido.</small></span>
                </label>
            </div>
        </div>
    </div>

    <div class="inv-tabla-wrap mt-3">
        <div class="table-responsive">
            <table class="table inv-tabla" data-filtro-rapido>
                <thead>
                    <tr>
                        <th scope="col">Producto</th>
                        <th scope="col" style="min-width:150px">Recibido hasta ahora</th>
                        <th scope="col">Comentario anterior</th>
                        <th scope="col">Cantidad recibida ahora</th>
                        <th scope="col">Comentario</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($SoliPendi as $item)
                        @if ($item->Completas == 0)
                            <tr>
                                <td class="font-weight-bold">
                                    {{ $item->Producto }}
                                    <input type="hidden" name="IdItem[]" value="{{ $item->Id }}">
                                    <input type="hidden" name="Inventario[]" value="{{ $item->Inventario }}">
                                    <input type="hidden" name="RegistroFactura" value="{{ $item->RegistroFactura }}">
                                </td>
                                @php $pct = $item->Cantidades > 0 ? min(100, round($item->Aprobadas / $item->Cantidades * 100)) : 0; @endphp
                                <td>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span><strong>{{ $item->Aprobadas + 0 }}</strong> de {{ $item->Cantidades + 0 }}</span>
                                        <span class="text-muted">{{ $pct }}%</span>
                                    </div>
                                    <div class="inv-avance"><span style="width: {{ $pct }}%"></span></div>
                                </td>
                                <td class="inv-texto-2">{{ $item->Comentario }}</td>
                                <td>
                                    <input type="number" name="Catidad[]" class="form-control form-control-sm" min="0" step="any"
                                           style="min-width: 110px;" autocomplete="off" aria-label="Cantidad recibida de {{ $item->Producto }}">
                                </td>
                                <td>
                                    <textarea name="Comentario[]" class="form-control form-control-sm" maxlength="150" rows="1"
                                              autocomplete="off" aria-label="Comentario de {{ $item->Producto }}"></textarea>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="inv-vacio"><i class="fas fa-inbox"></i><p>No hay productos pendientes de aprobar.</p></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="inv-acciones inv-acciones--fin mt-3">
        <a class="btn btn-outline-secondary" href="{{ route('SolicPendFacturas', ['id' => Crypt::encrypt($div->id)]) }}">{{ __('Cancelar') }}</a>
        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-check mr-1"></i> {{ __('Aprobar y continuar') }}</button>
    </div>
</form>
@push('js')
<script>
    // Pedido completo (2) no usa las cantidades recibidas: se deshabilitan para no enviarlas.
    $(function () {
        function ajustarCantidades() {
            var completo = $('input[name="Estado"]:checked').val() === '2';
            $('input[name="Catidad[]"]').prop('disabled', completo)
                .attr('placeholder', completo ? 'Completo' : '');
        }
        $('input[name="Estado"]').on('change', ajustarCantidades);
        ajustarCantidades();
    });
</script>
@endpush
@endsection
