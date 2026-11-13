{{--
    Panel con el resultado de la importación de tarjetas.
    Recibe $imp (session('importacion')) desde TarjetasBRController::redirectResultadoImportacion.
--}}
@php
    $cfg = [
        'ok' => ['color' => '#15803D', 'fondo' => '#eaf7ee', 'icono' => 'fa-check-circle',
            'titulo' => '¡Importación completada!',
            'texto' => "Se cargaron {$imp['importadas']} " . ($imp['importadas'] === 1 ? 'tarjeta' : 'tarjetas') . ' sin ningún problema.'],
        'parcial' => ['color' => '#B45309', 'fondo' => '#fff8e1', 'icono' => 'fa-exclamation-circle',
            'titulo' => 'Importación completada con observaciones',
            'texto' => "Se cargaron {$imp['importadas']} de {$imp['filas']} tarjetas. Las {$imp['con_error']} filas de abajo no se cargaron: corrígelas y vuelve a subir solo esas."],
        'error' => ['color' => '#B91C1C', 'fondo' => '#fdecee', 'icono' => 'fa-times-circle',
            'titulo' => 'No se cargó ninguna tarjeta',
            'texto' => "Ninguna de las {$imp['filas']} filas se pudo cargar. Revisa los problemas, corrige el archivo y vuelve a subirlo."],
        'formato' => ['color' => '#B91C1C', 'fondo' => '#fdecee', 'icono' => 'fa-file-excel',
            'titulo' => 'El archivo no tiene el formato esperado',
            'texto' => 'No encontramos todas las columnas necesarias en la primera fila del archivo.'],
        'vacio' => ['color' => '#0E7490', 'fondo' => '#e8f6f8', 'icono' => 'fa-inbox',
            'titulo' => 'El archivo no tiene datos',
            'texto' => 'Solo encontramos encabezados o filas vacías. Agrega las tarjetas debajo de los encabezados.'],
    ][$imp['estado']] ?? null;

    $columnas = ['numero' => 'Número de tarjeta', 'nit' => 'NIT', 'valor' => 'Valor', 'estado' => 'Estado'];
    $errores = collect($imp['errores'] ?? []);
    $visibles = 8;
    // Resumen de causas: "La tarjeta ya está registrada (40)"
    $causas = $errores->countBy('mensaje')->sortDesc()->take(4);
@endphp

@if ($cfg)
    <div class="imp-panel card border-0 shadow-sm mb-4" style="border-left: 5px solid {{ $cfg['color'] }} !important;">
        <div class="card-body p-4">
            {{-- Encabezado --}}
            <div class="d-flex align-items-start">
                <div class="imp-icono mr-3" style="background: {{ $cfg['fondo'] }}; color: {{ $cfg['color'] }};">
                    <i class="fas {{ $cfg['icono'] }}"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-1 font-weight-bold" style="color: {{ $cfg['color'] }};">{{ $cfg['titulo'] }}</h5>
                    <p class="mb-1 text-muted">{{ $cfg['texto'] }}</p>
                    <small class="text-muted"><i class="far fa-file-alt mr-1"></i>{{ $imp['archivo'] }}</small>
                </div>
                <button type="button" class="close ml-2" aria-label="Cerrar" onclick="this.closest('.imp-panel').remove()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Cifras --}}
            @if (in_array($imp['estado'], ['ok', 'parcial', 'error'], true))
                <div class="row mt-4 text-center">
                    <div class="col-4">
                        <div class="imp-cifra">
                            <div class="imp-numero text-dark">{{ number_format($imp['filas'], 0, ',', '.') }}</div>
                            <div class="imp-etiqueta">Filas leídas</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="imp-cifra">
                            <div class="imp-numero" style="color:#15803D;">{{ number_format($imp['importadas'], 0, ',', '.') }}</div>
                            <div class="imp-etiqueta">Cargadas</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="imp-cifra">
                            <div class="imp-numero" style="color: {{ $imp['con_error'] ? '#B91C1C' : '#6B7280' }};">{{ number_format($imp['con_error'], 0, ',', '.') }}</div>
                            <div class="imp-etiqueta">Con problemas</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Columnas faltantes --}}
            @if ($imp['estado'] === 'formato')
                <div class="mt-4">
                    <div class="font-weight-bold mb-2">Columnas necesarias</div>
                    <div class="d-flex flex-wrap">
                        @foreach ($columnas as $clave => $nombre)
                            @php $falta = in_array($clave, $imp['columnas_faltantes'], true); @endphp
                            <span class="imp-chip {{ $falta ? 'imp-chip-falta' : 'imp-chip-ok' }}">
                                <i class="fas {{ $falta ? 'fa-times' : 'fa-check' }} mr-1"></i>{{ $nombre }}{{ $falta ? ' — falta' : '' }}
                            </span>
                        @endforeach
                    </div>
                    @if (!empty($imp['encabezados']))
                        <div class="font-weight-bold mt-3 mb-2">Tu archivo trae</div>
                        <div class="d-flex flex-wrap">
                            @foreach ($imp['encabezados'] as $h)
                                <span class="imp-chip imp-chip-gris">{{ $h }}</span>
                            @endforeach
                        </div>
                    @endif
                    <p class="small text-muted mt-3 mb-0">
                        La primera fila debe tener los títulos <b>numero, nit, valor, estado</b> (pueden tener tildes o más palabras, como “Número de tarjeta”).
                    </p>
                </div>
            @endif

            {{-- Problemas por fila --}}
            @if ($errores->isNotEmpty())
                <div class="mt-4">
                    {{-- Resumen de causas: solo útil cuando hay más errores de los que se ven en la tabla --}}
                    @if ($errores->count() > $visibles && $causas->count() > 0)
                        <div class="d-flex flex-wrap mb-2">
                            @foreach ($causas as $mensaje => $n)
                                <span class="imp-chip imp-chip-gris"><b class="mr-1">{{ $n }}</b> {{ \Illuminate\Support\Str::limit($mensaje, 60) }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="table-responsive imp-tabla">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Fila</th>
                                    <th style="width: 35%;">Tarjeta</th>
                                    <th>Problema</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($errores->take($visibles) as $e)
                                    <tr>
                                        <td><span class="badge badge-light border">{{ $e['fila'] }}</span></td>
                                        <td class="text-monospace small">{{ $e['tarjeta'] ?? '—' }}</td>
                                        <td>{{ $e['mensaje'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($errores->count() > $visibles)
                        <details class="mt-2">
                            <summary class="text-primary small" style="cursor:pointer;">
                                Ver las {{ $errores->count() - $visibles }} filas restantes
                            </summary>
                            <div class="table-responsive imp-tabla mt-2" style="max-height: 320px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach ($errores->slice($visibles) as $e)
                                            <tr>
                                                <td style="width: 15%;"><span class="badge badge-light border">{{ $e['fila'] }}</span></td>
                                                <td style="width: 35%;" class="text-monospace small">{{ $e['tarjeta'] ?? '—' }}</td>
                                                <td>{{ $e['mensaje'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @endif

                    @if ($imp['con_error'] > $errores->count())
                        <p class="small text-muted mt-2 mb-0">Se muestran las primeras {{ $errores->count() }} de {{ $imp['con_error'] }} filas con problemas.</p>
                    @endif
                </div>
            @endif

            {{-- Ayuda --}}
            @if ($imp['estado'] !== 'ok')
                <div class="mt-4 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between">
                    <small class="text-muted mb-2 mb-md-0">
                        <i class="fas fa-lightbulb mr-1 text-warning"></i>
                        @if ($imp['estado'] === 'parcial')
                            Las filas correctas ya quedaron cargadas: si vuelves a subir el archivo completo, esas aparecerán como “ya registrada”.
                        @else
                            No se guardó nada. Usa la plantilla como guía para el formato del archivo.
                        @endif
                    </small>
                    <a href="{{ route('BonoRegalo.PlantillaTarjetas') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download mr-1"></i> Descargar plantilla
                    </a>
                </div>
            @endif
        </div>
    </div>

    @push('css')
        <style>
            .imp-icono { width: 52px; height: 52px; min-width: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
            .imp-cifra { background: #F3F4F6; border-radius: 12px; padding: .9rem .5rem; }
            .imp-numero { font-size: 1.9rem; font-weight: 800; line-height: 1.1; }
            .imp-etiqueta { font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #4B5563; margin-top: .2rem; }
            .imp-chip { display: inline-flex; align-items: center; border-radius: 999px; padding: .3rem .8rem; margin: 0 .4rem .4rem 0; font-size: .85rem; }
            .imp-chip-ok { background: #eaf7ee; color: #15803D; }
            .imp-chip-falta { background: #fdecee; color: #B91C1C; font-weight: 600; }
            .imp-chip-gris { background: #F3F4F6; color: #1F2937; }
            .imp-tabla { border: 1px solid #eef0f2; border-radius: 10px; }
            .imp-tabla thead th { background: #F3F4F6; border-top: 0; font-size: .78rem; text-transform: uppercase; color: #4B5563; }
            .imp-tabla td { vertical-align: middle; }
        </style>
    @endpush
@endif
