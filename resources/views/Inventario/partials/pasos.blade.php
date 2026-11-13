{{-- Indicador de pasos de un flujo.
     Uso: @include('Inventario.partials.pasos', ['pasos' => ['Datos', 'Productos'], 'actual' => 1]) --}}
<ol class="inv-pasos" aria-label="Pasos del proceso">
    @foreach ($pasos as $i => $nombre)
        @php $n = $i + 1; @endphp
        <li class="inv-paso {{ $n < $actual ? 'is-hecho' : ($n === $actual ? 'is-activo' : '') }}"
            @if ($n === $actual) aria-current="step" @endif>
            <span class="inv-paso__num">
                @if ($n < $actual)<i class="fas fa-check"></i>@else{{ $n }}@endif
            </span>
            <span class="inv-paso__txt">{{ $nombre }}</span>
        </li>
    @endforeach
</ol>
