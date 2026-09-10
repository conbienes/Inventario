<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recibo {{ $factura->numero_factura }}</title>
  <style>
    /* Márgenes de página */
    @page { margin: 28mm 18mm 22mm 18mm; } /* sup, der, inf, izq */

    /* Tipos y utilidades */
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#000; }
    .header { text-align:center; line-height:1.2; margin-bottom:18px; }
    .title { font-size:16px; font-weight:bold; }
    .right { text-align:right; }
    .center { text-align:center; }

    table { width:100%; border-collapse:collapse; }
    th, td { padding:6px; vertical-align:top; }

    /* Caja superior (sin línea central) */
    .box { border:1px solid #000; }
    .box td { border:none; } /* <- sin bordes internos, se elimina la línea del centro */

    /* Tabla de ítems (con líneas) */
    .items th, .items td { border:1px solid #000; }
    .items th { font-weight:bold; }

    /* Nota fija al final */
    .note-fixed {
      position: fixed;
      left: 18mm; right: 18mm; bottom: 12mm;   /* un poco encima del margen inferior */
      font-size:10px; text-align:justify;
    }
  </style>
</head>
<body>
@php
  $fmt = fn($v) => number_format((float)$v, 0, ',', '.'); // 75.000
  $fechaStr = \Carbon\Carbon::parse($factura->fecha)->locale('es')->translatedFormat('d \\de F \\de Y');
  $empresa = $empresa ?? [
      'nombre'    => 'ADMINISTRACION MAYORCA S.A.S',
      'nit'       => '901344877-7',
      'direccion' => 'CL 51 SUR 48 57 ET1 P8',
      'telefono'  => '6042333',
      'titulo'    => 'RECIBOS DE CAJA',
  ];
@endphp

{{-- Encabezado --}}
<div class="header">
  <div class="title">{{ $empresa['nombre'] }}</div>
  <div>{{ $empresa['nit'] }}</div>
  <div>{{ $empresa['direccion'] }}</div>
  <div>{{ $empresa['telefono'] }}</div>
</div>

{{-- Bloque datos + número SIN línea central --}}
<table class="box">
  <tr>
    <td>
      <strong>Fecha:</strong> {{ $fechaStr }}<br>
      <strong>Cliente:</strong> {{ $cliente->nombre }} {{ $cliente->apellidos }}<br>
      <strong>NIT:</strong> {{ $cliente->cedula }}<br>
      <strong>EMAIL:</strong> {{ $cliente->correo }}
    </td>
    <td class="right">
      <div style="font-size:10px;">{{ $empresa['titulo'] }}</div>
      <div><strong>No. {{ $factura->numero_factura }}</strong></div>
    </td>
  </tr>
</table>

{{-- Ítems --}}
<table class="items" style="margin-top:12px;">
  <thead>
    <tr>
      <th>Nº Tarjeta</th>
      <th>Descripción</th>
      <th class="center">Cantidad</th>
      <th class="right">Valor</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($items as $it)
      <tr>
        <td>{{ $it['numero_tarjeta'] }}</td>
        <td>TARJETA BONO REGALO</td>
        <td class="center">1</td>
        <td class="right">$ {{ $fmt($it['precio']) }}</td>
      </tr>
    @endforeach
    <tr>
      <td></td>
      <td class="right"><strong>TOTAL</strong></td>
      <td class="center">{{ count($items) }}</td>
      <td class="right"><strong>$ {{ $fmt($factura->total) }}</strong></td>
    </tr>
  </tbody>
</table>

{{-- Nota fija al final de la página --}}
<div class="note-fixed">
  <strong>NOTA:</strong> La adquisición de los bonos de regalo no da lugar a la obligación de facturar
  por no enmarcarse en la venta de un bien o prestación de un servicio (Artículo 615 E.T.), por lo cual,
  dicha operación no cuenta con una factura de venta.
</div>

</body>
</html>
