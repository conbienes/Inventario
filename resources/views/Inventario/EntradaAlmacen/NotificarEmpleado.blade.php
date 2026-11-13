@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/lte/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@push('js')
<script src="{{ asset('assets/lte/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $('#Correo').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Busque un correo',
        language: {
            noResults: function () { return 'Correo no encontrado'; },
            searching: function () { return 'Buscando...'; }
        },
        ajax: {
            url: "{{ url('/Ajax/Correos') }}",
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return { text: item.Correo, id: item.Id };
                    })
                };
            },
            cache: true
        }
    });
</script>
@endpush

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Notificar al personal') }}</h1>
        <p class="inv-subtitulo">Envíe un resumen de los productos recibidos en esta entrega.</p>
    </div>
</div>

@include('Inventario.partials.pasos', ['pasos' => ['Revisar lo recibido', 'Calificar proveedor', 'Notificar al personal'], 'actual' => 3])

@if (!empty($mensajeError ?? null))
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle fa-lg mr-3"></i>
        <div>{{ $mensajeError }}</div>
    </div>
@endif

@if (($Enviado ?? 0) == 1)
    <div class="alert alert-success d-flex align-items-center" role="alert">
        <i class="fas fa-paper-plane fa-lg mr-3"></i>
        <div><strong>¡Notificación enviada!</strong><br>Puede enviarla a otra persona o terminar.</div>
    </div>
@endif

<div class="row justify-content-center"><div class="col-lg-8">
<div class="inv-tarjeta-wrap">
    <form method="POST" action="{{ route('NotificarPersonal') }}" id="formNotificar" class="inv-tarjeta p-3 p-md-4">
        @csrf
        <input type="hidden" name="registroF" value="{{ $registroF }}">

        <div class="form-group mb-0">
            <label for="Correo">{{ __('Correo electrónico del destinatario') }} <span class="inv-campo-obligatorio">*</span></label>
            <select id="Correo" name="Correo" class="form-control" required></select>
            <small class="inv-ayuda">Se enviará un resumen de los productos recibidos en esta entrega.</small>
        </div>

        <div class="inv-acciones inv-acciones--fin mt-4">
            <a class="btn btn-outline-secondary" href="{{ route('EntAlmacen') }}"><i class="fas fa-flag-checkered mr-1"></i> {{ __('Terminar') }}</a>
            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-paper-plane mr-1"></i> {{ __('Enviar notificación') }}</button>
        </div>
    </form>
</div>
</div></div>
@endsection
