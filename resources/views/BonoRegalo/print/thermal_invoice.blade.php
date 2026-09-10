<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Recibo de caja {{ $facturaNo }}</title>
<style>
  /* ↓ Baja a 76 mm para que no corte el borde derecho */
  @page { size: 76mm auto; margin: 2mm; }

  /* ↓ Acolchado extra a la derecha (gutter) */
  body {
    font-family: monospace;
    font-size: 12px;
    margin: 0;
    padding: 2mm 4mm 2mm 2mm; /* top right bottom left */
    color: #000;
  }

  .center { text-align: center; }
  .right  { text-align: right; }
  .small  { font-size: 10px; }

  table { width: 100%; border-collapse: collapse; table-layout: fixed; }
  td, th {
    padding: 2px 0;
    vertical-align: top;
    word-break: break-word;
    overflow-wrap: anywhere;
    /* Evita que los números se partan y empujen fuera del borde: */
    white-space: nowrap;
  }
  .hr  { border-top: 1px dashed #000; margin: 6px 0; }
  .gap-1cm { height: 10mm; }
  .note { font-size: 9px; line-height: 1.25; text-align: justify; }

  @media print { .no-print { display: none; } }
</style>
</head>
<body>
  <div class="center">
    <strong>ADMINISTRACION MAYORCA S.A.S </strong><br>
    <span class="small">CL 51 SUR 48 57 ET1 P8</span><br>
    <span class="small">6042333</span>
  </div>

  <div class="hr"></div>

  <div>
    <div><strong>Recibo Caja:</strong> {{ $facturaNo }}</div>
    <div><strong>Fecha:</strong> {{ $fechaMin ? $fechaMin->format('Y-m-d') : now()->format('Y-m-d') }}</div>
    @if(!empty($cedula))
      <div><strong>Cédula:</strong> {{ $cedula }}</div>
    @endif
  </div>

  <div class="hr"></div>

  <!-- ↓ Colgroup con anchos fijos (ligeramente más angosto el precio) -->
  <table>
    <colgroup>
      <col style="width: 44%;">
      <col style="width: 10%;">
      <col style="width: 22%;">
      <col style="width: 24%;"> <!-- TOTAL un poco más ancho -->
    </colgroup>
    <thead>
      <tr>
        <th>No. Tarjeta</th>
        <th class="center">Cant</th>
        <th class="right">Precio</th>
        <th class="right">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($lineas as $ln)
        @php
          $desc =$ln->tarjeta;
          $cant = 1;
          $precio = (float) $ln->valor_tarjeta;
          $lineTotal = $cant * $precio;
        @endphp
        <tr>
          <td>{{ $desc }}</td>
          <td class="center">{{ $cant }}</td>
          <td class="right">{{ number_format($precio, 0) }}</td>
          <td class="right">{{ number_format($lineTotal, 0) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="hr"></div>

  <table>
    <colgroup>
      <col style="width: 76%;">
      <col style="width: 24%;">
    </colgroup>
    <tr>
      <td class="right"><strong>TOTAL:</strong></td>
      <td class="right">{{ number_format($total, 0) }}</td>
    </tr>
  </table>

  <div class="gap-1cm"></div>

  <div class="note">
    <strong>NOTA:</strong>
    La adquisición de los bonos de regalo no da lugar a la obligación de facturar por no enmarcarse en la venta de un bien o prestación de un servicio (Artículo 615 E.T.), por lo cual, dicha operación no cuenta con una factura de venta.
  </div>

  <div class="center small no-print" style="margin-top:10px;">
    <button onclick="window.print()">Imprimir</button>
  </div>
</body>
</html>
