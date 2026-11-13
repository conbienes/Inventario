@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@push('js')
<script>
    $(function () {
        var $grupo = $('#calificacion');
        var seleccion = parseInt($grupo.find('input:checked').val(), 10) || 0;

        function pintar(valor) {
            $grupo.find('label').each(function () {
                $(this).toggleClass('inv-estrella-activa', parseInt($(this).data('valor'), 10) <= valor);
            });
        }

        pintar(seleccion);

        $grupo.find('input').on('change', function () {
            seleccion = parseInt($(this).val(), 10);
            pintar(seleccion);
        });
        $grupo.find('label').on('mouseenter', function () {
            pintar(parseInt($(this).data('valor'), 10));
        }).on('mouseleave', function () {
            pintar(seleccion);
        });
    });
</script>
@endpush

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Calificar la entrega de productos') }}</h1>
        <p class="inv-subtitulo">Evalúe la entrega del proveedor para esta remisión.</p>
    </div>
</div>

@include('Inventario.partials.pasos', ['pasos' => ['Revisar lo recibido', 'Calificar proveedor', 'Notificar al personal'], 'actual' => 2])

<div class="row justify-content-center"><div class="col-lg-8">
<div class="inv-tarjeta-wrap">
    <form method="POST" action="{{ route('CalificarPost') }}" class="inv-tarjeta p-3 p-md-4">
        @csrf
        <input type="hidden" name="registroF" value="{{ $registroF }}">

        <div class="form-group">
            <span id="etiqueta-calificacion" class="font-weight-bold d-block mb-1">¿Cómo fue la entrega del proveedor? <span class="inv-campo-obligatorio">*</span></span>
            <div id="calificacion" class="inv-estrellas" role="radiogroup" aria-labelledby="etiqueta-calificacion">
                @for ($i = 1; $i <= 5; $i++)
                    <input type="radio" id="estrella{{ $i }}" name="estrellas" value="{{ $i }}" required
                           aria-label="{{ $i }} de 5 estrellas">
                    <label for="estrella{{ $i }}" data-valor="{{ $i }}" title="{{ $i }} de 5">&#9733;</label>
                @endfor
            </div>
            <small class="inv-ayuda d-block mt-1">1 = muy mala, 5 = excelente.</small>
        </div>

        <div class="form-group mb-0">
            <label for="CCalificacion">{{ __('Comentario') }} <span class="inv-campo-obligatorio">*</span></label>
            <textarea id="CCalificacion" name="CCalificacion" class="form-control" rows="3" maxlength="200"
                      autocomplete="off" required></textarea>
            <small class="inv-ayuda">Máximo 200 caracteres.</small>
        </div>

        <div class="inv-acciones inv-acciones--fin mt-4">
            <a class="btn btn-outline-secondary" href="{{ route('EntAlmacen') }}">{{ __('Volver al inicio') }}</a>
            <button type="submit" class="btn btn-success btn-lg">{{ __('Guardar y continuar') }} <i class="fas fa-arrow-right ml-1"></i></button>
        </div>
    </form>
</div>
</div></div>
@endsection
