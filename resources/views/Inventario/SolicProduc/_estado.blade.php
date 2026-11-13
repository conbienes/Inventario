{{-- Badge de estado de una solicitud de Inventario (usa las clases del estandar inv-estado). Uso: @include('Inventario.SolicProduc._estado', ['estado' => $item->Estado]) --}}
@if ((int) $estado === 2)
    <span class="inv-estado inv-estado--ok">Aprobada</span>
@else
    <span class="inv-estado inv-estado--alerta">Pendiente</span>
@endif
