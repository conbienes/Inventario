<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Resumen de Ventas</title>
<style>
  @page { size: {{ $Wmm ?? 76 }}mm auto; margin: 2mm; }

  body{
    font-family: monospace;
    font-size: 12px;
    margin: 0;
    padding: 2mm 4mm 2mm 2mm; /* top right bottom left */
    color:#000;
  }

  .center{ text-align:center; }
  .right{ text-align:right; }
  .small{ font-size:10px; }
  .muted{ color:#444; }

  table{ width:100%; border-collapse:collapse; table-layout:fixed; }
  th, td{
    padding: 2px 0;
    vertical-align: top;
    overflow-wrap: anywhere;
    white-space: nowrap;
  }

  .hr{ border-top:1px dashed #000; margin:6px 0; }
  .gap-1cm{ height:10mm; }

  .no-print{ margin-top:8px; }
  @media print{ .no-print{ display:none; } }

  /* Encabezados de sección */
  .sec-title{ font-weight:bold; }
</style>
</head>
<body>

  {{-- Encabezado --}}
  <div class="center">
    <strong>{{ $empresa['nombre'] ?? 'Mi Comercio' }}</strong><br>
    <span class="small">{{ $empresa['dir'] ?? '' }}</span><br>
    @if(!empty($empresa['tel'])) <span class="small">{{ $empresa['tel'] }}</span>@endif
  </div>

  <div class="hr"></div>

  {{-- Info general --}}
  <div>
    <div><strong>Resumen de Ventas</strong></div>
    <div class="small">Usuario: {{ $userName }}</div>
    <div class="small">Rango: {{ $fromDT->toDateString() }} a {{ $toDT->toDateString() }}</div>
    <div class="small">Emitido: {{ now()->format('Y-m-d H:i') }}</div>
  </div>

  <div class="hr"></div>

  {{-- TARJETAS (solo tarjetas + precio) --}}
  <div class="sec-title">Tarjetas</div>
  <table>
    <colgroup>
      <col style="width:60%;">
      <col style="width:40%;">
    </colgroup>
    <thead>
      <tr>
        <th>No. Tarjeta</th>
        <th class="right">Precio</th>
      </tr>
    </thead>
    <tbody>
      @forelse($tarjetas as $t)
        <tr>
          <td>{{ $t['numero'] }}</td>
          <td class="right">{{ number_format($t['valor'], 0, ',', '.') }}</td>
        </tr>
      @empty
        <tr><td colspan="2" class="muted">Sin tarjetas en el rango.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="hr"></div>

  {{-- Totales tarjetas (cantidad y total) --}}
  <table>
    <colgroup>
      <col style="width:60%;">
      <col style="width:40%;">
    </colgroup>
    <tr>
      <td class="right"><strong>Cant. tarjetas:</strong></td>
      <td class="right">{{ $tarjetasCount }}</td>
    </tr>
    <tr>
      <td class="right"><strong>Total tarjetas:</strong></td>
      <td class="right">{{ number_format($tarjetasTotal, 0, ',', '.') }}</td>
    </tr>
  </table>

  <div class="hr"></div>

  {{-- MÉTODOS DE PAGO (método y cuánto es) --}}
  <div class="sec-title">Medios de pago</div>
  <table>
    <colgroup>
      <col style="width:60%;">
      <col style="width:40%;">
    </colgroup>
    <thead>
      <tr>
        <th>Método</th>
        <th class="right">Total</th>
      </tr>
    </thead>
    <tbody>
      @forelse($metodos as $m)
        <tr>
          <td>{{ $m['name'] }} ({{ $m['n'] }})</td>
          <td class="right">{{ number_format($m['total'], 0, ',', '.') }}</td>
        </tr>
      @empty
        <tr><td colspan="2" class="muted">Sin pagos en el rango.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="hr"></div>

  {{-- Total recibido --}}
  <table>
    <colgroup>
      <col style="width:60%;">
      <col style="width:40%;">
    </colgroup>
    <tr>
      <td class="right"><strong>Total recibido:</strong></td>
      <td class="right">{{ number_format($totalRecibido, 0, ',', '.') }}</td>
    </tr>
  </table>

  <div class="gap-1cm"></div>

  {{-- Nota --}}
  <div class="small">
    <strong>NOTA:</strong>
    Cuadre de Bono Regalo.
  </div>

  <div class="gap-1cm"></div>

  <div class="center small no-print">
    <button onclick="window.print()">Imprimir</button>
  </div>

  {{-- (Opcional) auto print
  <script>window.onload = () => window.print();</script>
  --}}
</body>
</html>
