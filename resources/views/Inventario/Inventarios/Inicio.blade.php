@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@if (session('succes'))
    <div class="alert alert-success mb-3" role="alert">{{ session('succes') }}</div>
@endif
@if (session('succes1'))
    <div class="alert inv-alerta-ambar mb-3" role="alert">{{ session('succes1') }}</div>
@endif

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Inventario por división') }}</h1>
        <p class="inv-subtitulo">Seleccione una división para consultar su inventario.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('InicioInventario') }}">{{ __('Volver') }}</a>
        <a href="{{ route('Crear_Inventario') }}" class="btn btn-success"><i class="fas fa-plus"></i> {{ __('Crear') }}</a>
    </div>
</div>

<div class="row">
    @forelse ($Division as $item)
        <div class="col-12 col-sm-6 col-lg-4 mb-3">
            <a class="inv-tarjeta-enlace d-flex align-items-center p-3 h-100" href="{{ route('Consultar_Inventario', ['id' => Crypt::encrypt($item->id)]) }}">
                <img src="{{ asset("assets/$theme/dist/img/6.png") }}" alt="" width="48" height="48" class="mr-3">
                <span class="font-weight-bold">{{ $item->nombre }}</span>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="inv-vacio"><i class="fas fa-folder-open"></i><p>No hay divisiones registradas.</p></div>
        </div>
    @endforelse
</div>
@endsection
