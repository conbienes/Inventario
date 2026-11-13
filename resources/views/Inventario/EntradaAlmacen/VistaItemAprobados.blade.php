@extends("theme.$theme.layout")
@section('body-class', 'inv-page')
@include('Inventario.partials.estilos')

@section('content')
<div class="inv-cabecera">
    <div>
        <h1 class="inv-titulo">{{ __('Productos aprobados del pedido') }}</h1>
        <p class="inv-subtitulo">Cantidades aprobadas y solicitadas de cada producto.</p>
    </div>
    <div class="inv-acciones">
        <a class="btn btn-outline-secondary" href="javascript:history.back()"><i class="fas fa-arrow-left"></i> {{ __('Volver') }}</a>
    </div>
</div>

<div class="inv-tabla-wrap">
    <div class="table-responsive">
        <table class="table inv-tabla" data-filtro-rapido>
            <thead>
                <tr>
                    <th scope="col">Producto</th>
                    <th scope="col">Comentario</th>
                    <th scope="col" style="min-width:170px">Recibido</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($itemfactura as $item)
                    <tr>
                        @php $pct = $item->CantidadS > 0 ? min(100, round($item->CantidadA / $item->CantidadS * 100)) : 0; @endphp
                        <td class="font-weight-bold">{{ $item->Producto }}</td>
                        <td class="inv-texto-2">{{ $item->Comentario ?: '—' }}</td>
                        <td>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><strong>{{ $item->CantidadA + 0 }}</strong> de {{ $item->CantidadS + 0 }}</span>
                                <span class="text-muted">{{ $pct }}%</span>
                            </div>
                            <div class="inv-avance"><span style="width: {{ $pct }}%"></span></div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <div class="inv-vacio"><i class="fas fa-inbox"></i><p>Este pedido no tiene productos registrados.</p></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
