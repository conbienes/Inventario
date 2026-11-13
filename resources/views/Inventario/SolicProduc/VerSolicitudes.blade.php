@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@php
    $cabecera = $pedidoinventario->first();
@endphp

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Detalle de la solicitud #{{ $cabecera->IdSolicitud }}</h1>
        <p class="inv-subtitulo">Productos solicitados y estado actual.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="{{ route('Pendientes') }}"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="inv-tarjeta-wrap mb-3">
    <dl class="row inv-tarjeta p-3 p-md-4 mb-0">
        <dt class="col-sm-3">Solicitante</dt>
        <dd class="col-sm-9">{{ $cabecera->NombreE }} {{ $cabecera->ApellidosE }}</dd>
        <dt class="col-sm-3">Área</dt>
        <dd class="col-sm-9">{{ $cabecera->AreaS }}</dd>
        <dt class="col-sm-3">Fecha tentativa</dt>
        <dd class="col-sm-9">{{ $cabecera->FechaTentativa ?? '-' }}</dd>
        <dt class="col-sm-3">Observaciones</dt>
        <dd class="col-sm-9">{{ $cabecera->Observaciones ?: '-' }}</dd>
        <dt class="col-sm-3">Estado</dt>
        <dd class="col-sm-9">@include('Inventario.SolicProduc._estado', ['estado' => $cabecera->Estado])</dd>
    </dl>
</div>

<div class="inv-tabla-wrap">
    <div class="table-responsive">
        <table class="table inv-tabla" data-filtro-rapido>
            <thead>
                <tr>
                    <th scope="col">Producto</th>
                    <th scope="col" class="num">Cantidad solicitada</th>
                    <th scope="col">Fecha solicitud</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedidoinventario as $item)
                    <tr>
                        <td>{{ $item->NombreP }}</td>
                        <td class="num">{{ $item->Cantidad }}</td>
                        <td>{{ $item->Fecha }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
