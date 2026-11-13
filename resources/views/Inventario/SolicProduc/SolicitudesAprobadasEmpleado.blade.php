@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@php
    $cabecera = $pedidoinventario->first();
    $completa = $pedidoinventario->filter(fn ($l) => (int) $l->CantAprobadas !== (int) $l->Cantidad)->isEmpty();
@endphp

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Detalle de la solicitud aprobada #{{ $cabecera->IdSolicitud }}</h1>
        <p class="inv-subtitulo">Resultado de la aprobación de los productos solicitados.</p>
    </div>
    <div class="inv-acciones">
        @if (tienePermisoModulo(auth()->id(), 1, [1, 2]))
            <a class="btn btn-outline-secondary" href="{{ route('TotalAprobadas') }}"><i class="fas fa-arrow-left"></i> Volver</a>
        @else
            <a class="btn btn-outline-secondary" href="{{ route('Aprobadas') }}"><i class="fas fa-arrow-left"></i> Volver</a>
        @endif
    </div>
</div>

<div class="inv-tarjeta-wrap mb-3">
    <dl class="row inv-tarjeta p-3 p-md-4 mb-0">
        <dt class="col-sm-3">Solicitante</dt>
        <dd class="col-sm-9">{{ $cabecera->NombreEmp }} {{ $cabecera->ApellidoEmp }}</dd>
        <dt class="col-sm-3">Área</dt>
        <dd class="col-sm-9">{{ $cabecera->AreaS }}</dd>
        <dt class="col-sm-3">Aprobado por</dt>
        <dd class="col-sm-9">{{ $cabecera->NombreAprueba ? $cabecera->NombreAprueba . ' ' . $cabecera->ApellidosAprueba : '-' }}</dd>
        <dt class="col-sm-3">Fecha de aprobación</dt>
        <dd class="col-sm-9">{{ $cabecera->Fecha ?? '-' }}</dd>
        <dt class="col-sm-3">Resultado</dt>
        <dd class="col-sm-9">{{ $completa ? 'Entrega completa' : 'Entrega parcial' }}</dd>
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
                    <th scope="col" class="num">Cantidad aprobada</th>
                    <th scope="col">Comentario</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedidoinventario as $item)
                    <tr>
                        <td>{{ $item->NombreProduc }}</td>
                        <td class="num">{{ $item->Cantidad }}</td>
                        <td class="num">{{ $item->CantAprobadas ?? '-' }}</td>
                        <td>{{ $item->Comentario }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
