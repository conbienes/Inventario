<table border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>DIVISIÓN</th>
            <th>NIT</th>
            <th>PROVEEDOR</th>
            <th>FACTURA</th>
            <th>PRECIO TOTAL</th>
            <th>ITEM PRODUCTOS</th>
            <th>CANTIDAD SOLICITADA</th>
            <th>CANTIDAD RECIBIDA</th>
            <th>CALIFICACIÓN</th>
            <th>COMENTARIO</th>
            <th>ORDEN DE PEDIDO</th>
            <th>ESTADO</th>
            <th>FECHA CREACIÓN</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($EntradasAlmacen as $item)
        <tr>
            <td>{{ $item->Id }}</td>
            <td>{{ $item->Division ?? 'N/A' }}</td>
            <td>{{ $item->Nit ?? 'N/A' }}</td>
            <td>{{ $item->Proveedor ?? 'N/A' }}</td>
            <td>{{ $item->Factura ?? 'N/A' }}</td>
            <td>{{ number_format($item->PrecioTotal, 2, ',', '.') }}</td>
            <td>{{ $item->ItemProductos ?? 'N/A' }}</td>
            <td>{{ $item->CantidadSolicitada ?? '0' }}</td>
            <td>{{ $item->CantidadRecibidas ?? '0' }}</td>
            <td>{{ $item->Calificacion ?? 'Sin calificar' }}</td>
            <td>{{ $item->Comentario ?? '' }}</td>
            <td>{{ $item->OrdenPedido ?? 'N/A' }}</td>
            <td>{{ $item->Estado ?? 'Desconocido' }}</td>
            <td>{{ \Carbon\Carbon::parse($item->FechaCreacion)->format('Y-m-d H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
