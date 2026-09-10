<div>
    <h5>Factura: {{ $factura->factura }}</h5>
    <p>Fecha: {{ \Carbon\Carbon::parse($factura->fecha)->format('Y-m-d H:i') }}</p>
    <p>Cliente / Cédula: {{ $factura->cliente_nombre ?? '' }} / {{ $factura->cedula }}</p>

    <table class="table table-sm table-borderless">
        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-right">Cant</th>
                <th class="text-right">Precio</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $it)
                <tr>
                    <td>{{ $it->descripcion }}</td>
                    <td class="text-right">{{ $it->cantidad }}</td>
                    <td class="text-right">{{ number_format($it->precio, 2) }}</td>
                    <td class="text-right">{{ number_format($it->cantidad * $it->precio, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-right font-weight-bold">
        Total: ${{ number_format($factura->total, 2) }}
    </div>

    <div class="mt-3 d-flex justify-content-end">
        <a href="{{ route('BonoRegalo.printFactura', $factura->id) }}" target="_blank" class="btn btn-outline-primary mr-2" id="btn-print-window">
            <i class="fas fa-print"></i> Abrir vista para imprimir
        </a>

        <!-- Si quieres imprimir desde servidor via AJAX -->
        <button class="btn btn-primary" id="btn-print-server" data-id="{{ $factura->id }}">
            <i class="fas fa-print"></i> Imprimir (servidor)
        </button>
    </div>
</div>
