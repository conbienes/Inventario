@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
@php
    $cabecera = $pedidoinventario->first();
    $pendiente = (int) $cabecera->Estado === 1;
@endphp

<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">Revisar solicitud #{{ $cabecera->IdSolicitud }}</h1>
        <p class="inv-subtitulo">{{ $pendiente ? 'Indique la cantidad que se aprueba para cada producto.' : 'Solicitud ya revisada, en modo consulta.' }}</p>
    </div>
    @if (! $pendiente)
        <div class="inv-acciones">
            <a class="btn btn-outline-secondary" href="{{ route('Solicitudes') }}"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
    @endif
</div>

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="inv-tarjeta-wrap mb-3">
    <dl class="row inv-tarjeta p-3 p-md-4 mb-0">
        <dt class="col-sm-3">Solicitante</dt>
        <dd class="col-sm-9">{{ $cabecera->NombreEmp }} {{ $cabecera->ApellidoEmp }}</dd>
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

@if ($pendiente)
    <form method="POST" action="{{ route('AprobarSolicitud') }}" id="formAprobar">
        @csrf
        <input type="hidden" name="IdSolicitud" value="{{ $cabecera->IdSolicitud }}">

        <div class="inv-tabla-wrap">
            <div class="table-responsive">
                <table class="table inv-tabla" data-filtro-rapido>
                    <thead>
                        <tr>
                            <th scope="col">Producto</th>
                            <th scope="col" class="num">Solicitado</th>
                            <th scope="col" class="num">Existencia</th>
                            <th scope="col">Cantidad a aprobar</th>
                            <th scope="col">Comentario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedidoinventario as $item)
                            <tr>
                                <td>
                                    {{ $item->NombreProduc }}
                                    <input type="hidden" name="IdPedido[]" value="{{ $item->IdPedido }}">
                                    <input type="hidden" name="IdInventario[]" value="{{ $item->IdInventario }}">
                                </td>
                                <td class="num">{{ $item->Cantidad }}</td>
                                <td class="num">
                                    {{ $item->Cantidades }}
                                    @if ((int) $item->Cantidades < (int) $item->Cantidad)
                                        <br><span class="inv-estado inv-estado--critico">Insuficiente</span>
                                    @endif
                                </td>
                                <td>
                                    <label class="sr-only" for="cantidad{{ $loop->index }}">Cantidad a aprobar de {{ $item->NombreProduc }}</label>
                                    <input type="number" class="form-control" id="cantidad{{ $loop->index }}" name="Catidad[]"
                                        min="0" max="{{ $item->Cantidad }}" step="1" required
                                        value="{{ min((int) $item->Cantidad, max((int) $item->Cantidades, 0)) }}">
                                </td>
                                <td>
                                    <label class="sr-only" for="comentario{{ $loop->index }}">Comentario de {{ $item->NombreProduc }}</label>
                                    <input type="text" class="form-control" id="comentario{{ $loop->index }}" name="Comentario[]"
                                        maxlength="200" autocomplete="off">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p class="inv-ayuda mt-2 mb-3">Las cantidades aprobadas descuentan el stock. Si una línea supera la existencia, no se aprueba ninguna línea.</p>

        <div class="inv-acciones inv-acciones--fin">
            <a class="btn btn-outline-secondary" href="{{ route('Solicitudes') }}">Cancelar</a>
            <button type="submit" class="btn btn-success">Aprobar pedido</button>
        </div>
    </form>
@else
    <div class="alert alert-info" role="status">Esta solicitud ya fue aprobada. Se muestra en modo consulta.</div>

    <div class="inv-tabla-wrap">
        <div class="table-responsive">
            <table class="table inv-tabla" data-filtro-rapido>
                <thead>
                    <tr>
                        <th scope="col">Producto</th>
                        <th scope="col" class="num">Solicitado</th>
                        <th scope="col" class="num">Aprobado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidoinventario as $item)
                        <tr>
                            <td>{{ $item->NombreProduc }}</td>
                            <td class="num">{{ $item->Cantidad }}</td>
                            <td class="num">{{ $item->CantAprobadas ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if ($pendiente)
    @push('js')
        <script>
            document.getElementById('formAprobar').addEventListener('submit', function (e) {
                if (!window.confirm('¿Aprobar este pedido? Se descontará el stock de los productos aprobados.')) {
                    e.preventDefault();
                }
            });
        </script>
    @endpush
@endif
@endsection
