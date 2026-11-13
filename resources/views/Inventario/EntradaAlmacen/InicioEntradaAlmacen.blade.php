@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@push('css')
<style>
    .inv-div-tarjeta {
        display: flex; align-items: center; gap: 1rem;
        background: #fff; border: 1px solid #E2E8F0; border-radius: 14px;
        padding: 1.1rem 1.2rem; height: 100%; color: var(--inv-texto); text-decoration: none;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .05), 0 8px 20px rgba(15, 23, 42, .04);
        transition: transform .12s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .inv-div-tarjeta:hover, .inv-div-tarjeta:focus {
        transform: translateY(-2px);
        border-color: var(--inv-primario);
        box-shadow: 0 10px 26px rgba(0, 84, 132, .14);
        color: var(--inv-texto); text-decoration: none;
    }
    .inv-div-icono {
        flex: 0 0 56px; height: 56px; border-radius: 14px;
        background: linear-gradient(135deg, #005484, #0A6AA0);
        color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
        box-shadow: 0 6px 16px rgba(0, 84, 132, .28);
    }
    .inv-div-nombre { font-weight: 700; font-size: 1.05rem; line-height: 1.25; }
    .inv-div-pista { font-size: .85rem; color: var(--inv-texto-2); }
    .inv-div-flecha { margin-left: auto; color: var(--inv-primario); font-size: 1.1rem; }
</style>
@endpush

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Entradas de almacén') }}</h1>
        <p class="inv-subtitulo">Elija una división para ver sus entradas, o registre una nueva remisión.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('InicioInventario') }}"><i class="fas fa-arrow-left mr-1"></i> {{ __('Volver') }}</a>
    </div>
</div>

@if (session('succes'))
    <div class="alert alert-success" role="alert"><i class="fas fa-check-circle mr-1"></i> {{ session('succes') }}</div>
@endif
@if (session('succes1'))
    <div class="alert inv-alerta-ambar" role="alert"><i class="fas fa-exclamation-triangle mr-1"></i> {{ session('succes1') }}</div>
@endif

<div class="row">
    @forelse ($Division as $item)
        <div class="col-12 col-md-6 col-xl-4 mb-3">
            <a class="inv-div-tarjeta" href="{{ route('Vista_EntAlmacen', ['id' => Crypt::encrypt($item->id)]) }}">
                <span class="inv-div-icono"><i class="fas fa-warehouse"></i></span>
                <span>
                    <span class="inv-div-nombre d-block">{{ $item->nombre }}</span>
                    <span class="inv-div-pista">Ver entradas y pendientes</span>
                </span>
                <i class="fas fa-chevron-right inv-div-flecha" aria-hidden="true"></i>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="inv-tarjeta-wrap">
                <div class="inv-vacio"><i class="fas fa-inbox"></i><p>No hay divisiones registradas.</p></div>
            </div>
        </div>
    @endforelse
</div>
@endsection
