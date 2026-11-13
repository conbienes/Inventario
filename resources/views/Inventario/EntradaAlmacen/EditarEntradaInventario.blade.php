@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@php($item = $SoliPendi->first())

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Editar pedido de compra') }}</h1>
        <p class="inv-subtitulo">Corrija el número de remisión o la orden de pedido.</p>
    </div>
</div>

<div class="row justify-content-center"><div class="col-lg-9">
<div class="inv-tarjeta-wrap">
    @if (! $item)
        <div class="inv-tarjeta p-3 p-md-4">
            <div class="inv-vacio"><i class="fas fa-exclamation-circle"></i><p>No se encontró el pedido solicitado.</p></div>
            <div class="inv-acciones inv-acciones--fin mt-3">
                <a class="btn btn-outline-secondary" href="{{ route('EntAlmacen') }}">{{ __('Volver') }}</a>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route('ActualizarPedido') }}" class="inv-tarjeta p-3 p-md-4">
            @csrf
            <input type="hidden" name="Id" value="{{ $item->Id }}">
            <input type="hidden" name="Div" value="{{ $item->Div }}">

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="inv-seccion"><i class="fas fa-info-circle"></i> Datos del pedido</div>
            <div class="inv-resumen">
                <div class="inv-dato"><small>Proveedor</small><strong>{{ $item->Proveedor }}</strong></div>
                <div class="inv-dato"><small>NIT</small><strong>{{ $item->Nit }}</strong></div>
                <div class="inv-dato"><small>Valor</small><strong>${{ number_format((float) $item->PrecioTotal, 0, ',', '.') }}</strong></div>
                <div class="inv-dato"><small>Ingreso</small><strong>{{ $item->FechaIngreso ? \Carbon\Carbon::parse($item->FechaIngreso)->locale('es')->isoFormat('D MMM YYYY') : '—' }}</strong></div>
            </div>

            <div class="inv-seccion"><i class="fas fa-pen"></i> Lo que puede corregir</div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="Nfactura">{{ __('Número de remisión') }} <span class="inv-campo-obligatorio">*</span></label>
                    <input type="text" id="Nfactura" name="Nfactura" class="form-control" value="{{ old('Nfactura', $item->Nfactura) }}" autocomplete="off" required>
                    <small class="inv-ayuda">Solo letras, números, guiones y guion bajo.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="OrdenPedido">{{ __('Orden de pedido') }} <span class="inv-campo-obligatorio">*</span></label>
                    <input type="text" id="OrdenPedido" name="OrdenPedido" class="form-control" value="{{ old('OrdenPedido', $item->OrdenPedido) }}" autocomplete="off" required>
                    <small class="inv-ayuda">Solo letras, números, guiones y guion bajo.</small>
                </div>
            </div>

            <div class="inv-acciones inv-acciones--fin mt-4">
                <a class="btn btn-outline-secondary" href="{{ route('EntAlmacen') }}">{{ __('Cancelar') }}</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> {{ __('Guardar cambios') }}</button>
            </div>
        </form>
    @endif
</div>
</div></div>
@endsection
