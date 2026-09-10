<?php
// app/Http/Controllers/BonoRegalo/ReporteVentasController.php
namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BonoRegalo\Payment;
use App\Models\BonoRegalo\FacturaBonoR;
use App\Models\BonoRegalo\ClienteBonoR;
use App\Models\BonoRegalo\TarjetaBonoR;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\BusinessCentralService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReporteVentasController extends Controller
{
    /**
     * Registra un log de error con contexto completo
     */
    private function logError($message, $context = [])
    {
        Log::channel('bono_regalo')->error($message, array_merge([
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
        ], $context));
    }

    /**
     * Registra un log de información
     */
    private function logInfo($message, $context = [])
    {
        Log::channel('bono_regalo')->info($message, array_merge([
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
        ], $context));
    }

    /**
     * Registra un log de advertencia
     */
    private function logWarning($message, $context = [])
    {
        Log::channel('bono_regalo')->warning($message, array_merge([
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
        ], $context));
    }

    /**
     * Registra un log de debug
     */
    private function logDebug($message, $context = [])
    {
        Log::channel('bono_regalo')->debug($message, array_merge([
            'timestamp' => now()->toDateTimeString(),
        ], $context));
    }

    /**
     * Registra una operación bulk con sus resultados
     */
    private function logBulkOperation($operation, $data, $errors = null)
    {
        Log::channel('bono_regalo')->info('BULK_OPERATION', [
            'operation' => $operation,
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
            'data' => $data,
            'errors' => $errors,
        ]);
    }

    public function index(Request $request)
    {
        try {
            $this->logInfo('Accediendo a reporte de ventas', [
                'from' => $request->input('from'),
                'to' => $request->input('to'),
            ]);

            $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
            $to = $request->input('to', now()->format('Y-m-d'));

            $fromDT = Carbon::parse($from)->startOfDay();
            $toDT = Carbon::parse($to)->endOfDay();
            $statusOk = ['approved', 'paid', 'success', 'completed'];

            $kpis = Payment::whereBetween('created_at', [$fromDT, $toDT])
                ->whereIn('status', $statusOk)
                ->selectRaw('COALESCE(SUM(amount),0) as total_vendido, COUNT(*) as transacciones')
                ->first();

            $totalVendido = (float) $kpis->total_vendido;
            $transacciones = (int) $kpis->transacciones;
            $ticketPromedio = $transacciones ? round($totalVendido / $transacciones, 2) : 0;

            $porMetodo = Payment::query()
                ->join('payment_methods as pm', 'pm.id', '=', 'payments.method_id')
                ->whereBetween('payments.created_at', [$fromDT, $toDT])
                ->whereIn('payments.status', $statusOk)
                ->groupBy('pm.name')
                ->orderByDesc(DB::raw('SUM(payments.amount)'))
                ->get([DB::raw('pm.name as metodo'), DB::raw('SUM(payments.amount) as total'), DB::raw('COUNT(*) as n')]);

            $tendencia = Payment::whereBetween('created_at', [$fromDT, $toDT])
                ->whereIn('status', $statusOk)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw('DATE(created_at)'))
                ->get([DB::raw('DATE(created_at) as fecha'), DB::raw('SUM(amount) as total')]);

            $ultimas = Payment::with(['method', 'invoice'])
                ->whereBetween('created_at', [$fromDT, $toDT])
                ->whereIn('status', $statusOk)
                ->latest()
                ->limit(10)
                ->get();

            $this->logInfo('Reporte generado exitosamente', [
                'totalVendido' => $totalVendido,
                'transacciones' => $transacciones,
            ]);

            return view('bono-regalo.reporte-ventas', [
                'from' => $fromDT->format('Y-m-d'),
                'to' => $toDT->format('Y-m-d'),
                'totalVendido' => $totalVendido,
                'transacciones' => $transacciones,
                'ticketPromedio' => $ticketPromedio,
                'porMetodo' => $porMetodo,
                'ultimas' => $ultimas,
                'labelsLinea' => $tendencia->pluck('fecha')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')),
                'dataLinea' => $tendencia->pluck('total'),
                'labelsDonut' => $porMetodo->pluck('metodo'),
                'dataDonut' => $porMetodo->pluck('total'),
            ]);

        } catch (\Throwable $e) {
            $this->logError('Error al generar reporte de ventas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            throw $e;
        }
    }

    // ==== EXPORT A EXCEL ====
    public function exportExcel(Request $request)
    {
        try {
            $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
            $to = $request->input('to', now()->format('Y-m-d'));

            $this->logInfo('Exportando Excel', ['from' => $from, 'to' => $to]);

            return \Excel::download(new \App\Exports\VentasExport($from, $to), "ventas_{$from}_{$to}.xlsx");
        } catch (\Throwable $e) {
            $this->logError('Error al exportar Excel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    // ==== EXPORT A PDF ====
    public function exportPdf(Request $request)
    {
        try {
            $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
            $to = $request->input('to', now()->format('Y-m-d'));

            $this->logInfo('Exportando PDF', ['from' => $from, 'to' => $to]);

            $statusOk = ['approved', 'paid', 'success', 'completed'];
            $data = Payment::with('method', 'invoice')
                ->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
                ->whereIn('status', $statusOk)
                ->get();

            $pdf = \PDF::loadView('bono-regalo.exports.ventas-pdf', [
                'from' => $from,
                'to' => $to,
                'rows' => $data,
            ])->setPaper([0, 0, 612, 792], 'portrait');

            return $pdf->download("ventas_{$from}_{$to}.pdf");
        } catch (\Throwable $e) {
            $this->logError('Error al exportar PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function Contabilidad(Request $request)
    {
        try {
            $this->logInfo('Accediendo a contabilidad', [
                'estado' => $request->input('estado'),
                'desde' => $request->input('desde'),
                'hasta' => $request->input('hasta'),
                'q' => $request->input('q'),
            ]);

            // Estados permitidos
            $allEstados = ['ENVIADO', 'ERROR', 'PENDIENTE'];

            // Filtros llegados por GET
            $estados = (array) $request->input('estado', $allEstados);
            $estados = array_values(array_intersect($estados, $allEstados));
            if (empty($estados)) {
                $estados = $allEstados;
            }

            $desde = $request->input('desde', now()->startOfMonth()->toDateString());
            $hasta = $request->input('hasta', now()->toDateString());
            $q = trim((string) $request->input('q', ''));

            $facturas = FacturaBonoR::with(['cliente:id,nombre', 'usuario:id,nombre'])
                ->withSum(
                    [
                        'payments as pagado' => function ($q2) {
                            $q2->whereIn('status', ['approved', 'paid', 'success', 'completed']);
                        },
                    ],
                    'amount',
                )
                ->whereIn('estado_bc', $estados)
                ->when($desde, fn($qq) => $qq->whereDate('fecha', '>=', $desde))
                ->when($hasta, fn($qq) => $qq->whereDate('fecha', '<=', $hasta))
                ->when($q, function ($qq) use ($q) {
                    $qq->where(function ($w) use ($q) {
                        $w->where('numero_factura', 'like', "%{$q}%")->orWhereHas('cliente', fn($c) => $c->where('nombre', 'like', "%{$q}%"));
                    });
                })
                ->orderByDesc('fecha')
                ->paginate(200)
                ->appends($request->query());

            $this->logInfo('Contabilidad cargada exitosamente', [
                'total_facturas' => $facturas->total(),
                'estados_filtrados' => $estados,
            ]);

            return view('BonoRegalo.Contabilidad.Index', compact('facturas', 'estados', 'allEstados', 'desde', 'hasta', 'q'));

        } catch (\Throwable $e) {
            $this->logError('Error al cargar contabilidad', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            throw $e;
        }
    }

    public function CargarBC(Request $req, BusinessCentralService $bc)
    {
        try {
            $this->logInfo('INICIO - CargarBC', [
                'ids' => $req->input('ids', []),
                'companyId' => $req->input('companyId', env('BC_COMPANY_ID')),
                'user_id' => auth()->id(),
            ]);

            // ========= 1) PRIMERA FASE: sacar info (preview) =========
            $ids = (array) $req->input('ids', []);

            // Normaliza y valida IDs
            $ids = array_values(array_unique(array_filter($ids, fn($v) => is_numeric($v))));
            if (empty($ids)) {
                $this->logWarning('Intento de carga sin IDs', ['request' => $req->all()]);
                return response()->json(['message' => 'Debe enviar ids[] de facturas.'], 422);
            }
            $ids = array_map('intval', $ids);

            $this->logInfo('IDs validados', ['count' => count($ids), 'ids' => $ids]);

            // Trae facturas con cliente e ítems (para leer tarjeta_id)
            $facturas = FacturaBonoR::query()
                ->with(['cliente:id,tipo_documento,cedula,nombre,segundo_nombre,apellidos,segundo_apellido,correo,razons', 'items:id,factura_id,tarjeta_id'])
                ->whereIn('id', $ids)
                ->orderByDesc('fecha')
                ->get();

            // IDs no encontrados
            $notFound = array_values(array_diff($ids, $facturas->pluck('id')->all()));

            if (!empty($notFound)) {
                $this->logWarning('IDs no encontrados', ['not_found' => $notFound]);
            }

            // Prefetch de todas las tarjetas referenciadas por los ítems
            $tarjetaIds = $facturas->flatMap(fn($f) => $f->items->pluck('tarjeta_id'))->filter()->unique()->values();
            $tarjetasMap = TarjetaBonoR::whereIn('id', $tarjetaIds)
                ->get(['id', 'numero', 'nit', 'valor', 'estado'])
                ->keyBy('id');

            $this->logInfo('Tarjetas encontradas', ['count' => $tarjetasMap->count()]);

            // Arma "preview": factura + cliente + tarjetas compradas
            $previewData = $facturas
                ->map(function ($f) use ($tarjetasMap) {
                    $tIds = $f->items->pluck('tarjeta_id')->filter()->unique()->values();

                    return [
                        'id' => $f->id,
                        'numero_factura' => $f->numero_factura,
                        'fecha' => $f->fecha ? \Carbon\Carbon::parse($f->fecha)->toDateString() : null,
                        'total' => (float) $f->total,
                        'cliente' => $f->cliente
                            ? [
                                'id' => $f->cliente->id,
                                'tipo_documento' => $f->cliente->tipo_documento,
                                'cedula' => $f->cliente->cedula,
                                'nombre' => $f->cliente->nombre,
                                'segundo_nombre' => $f->cliente->segundo_nombre,
                                'apellidos' => $f->cliente->apellidos,
                                'segundo_apellido' => $f->cliente->segundo_apellido,
                                'correo' => $f->cliente->correo,
                                'razons' => $f->cliente->razons,
                            ]
                            : null,
                        'tarjetas' => $tIds
                            ->map(function ($tid) use ($tarjetasMap) {
                                $t = $tarjetasMap->get($tid);
                                return $t
                                    ? [
                                        'id' => $t->id,
                                        'numero' => $t->numero,
                                        'nit' => $t->nit,
                                        'valor' => (float) $t->valor,
                                        'estado' => $t->estado,
                                    ]
                                    : null;
                            })
                            ->filter()
                            ->values(),
                    ];
                })
                ->values();

            // ========= 2) SEGUNDA FASE: cargar (enviar a BC) =========
            $companyId = $req->input('companyId', env('BC_COMPANY_ID'));

            if (!$companyId) {
                $this->logError('Falta companyId', ['request' => $req->all()]);
                return response()->json(['error' => 'Falta companyId'], 400);
            }

            // Parámetros BC (permiten override por request)
            $tipoDocumento = $req->input('tipoDocumento', 'Invoice');
            $glCuentaDetalle = $req->input('ctaDetalle', '11051002');
            $bankAccountNo = $req->input('ctaTotal', 'BANCOLOMBIA 6289 BR');
            $libroCodigo = $req->input('libroCodigo', '1_NIIF');
            $codigoConcepto = $req->input('codigoConcepto', '110409');
            $grupoImpuesto = $req->input('grupoImpuesto', 'G COMPR 0%_');
            $areaImpuesto = $req->input('areaImpuesto', 'CLI-NRI');
            $diario = $req->input('diario', 'BONOREGALO');
            $seccion = $req->input('seccion', 'BONO REG');

            $results = [];
            $errorLog = [];
            $successCount = 0;
            $errorCount = 0;

            $this->logInfo('Parámetros de BC', [
                'companyId' => $companyId,
                'tipoDocumento' => $tipoDocumento,
                'glCuentaDetalle' => $glCuentaDetalle,
                'bankAccountNo' => $bankAccountNo,
                'diario' => $diario,
                'seccion' => $seccion,
            ]);

            foreach ($facturas as $f) {
                $facturaCode = $f->numero_factura ?: 'BR' . $f->id;
                $this->logDebug("Procesando factura {$facturaCode}", [
                    'factura_id' => $f->id,
                    'cliente_id' => $f->cliente_id,
                ]);

                try {
                    // Fecha: usa la enviada o la de la factura
                    $fechaLinea = $req->input('fecha', $f->fecha ? \Carbon\Carbon::parse($f->fecha)->toDateString() : \Carbon\Carbon::now()->toDateString());
                    // Tercero: preferimos cedula del cliente; si no, puede venir en request
                    $tercero = $f->cliente->cedula ?? (string) $req->input('tercero', '');

                    if ($tercero === '') {
                        $errorMsg = 'Factura sin cliente.cedula y no se envió "tercero"';
                        $this->logError($errorMsg, ['factura_id' => $f->id, 'factura_code' => $facturaCode]);
                        $errorLog[] = [
                            'factura_id' => $f->id,
                            'numero_factura' => $facturaCode,
                            'error' => $errorMsg,
                        ];
                        $errorCount++;
                        $results[] = [
                            'factura_id' => $f->id,
                            'numero_factura' => $facturaCode,
                            'error' => $errorMsg,
                        ];
                        continue;
                    }

                    // Tarjetas asociadas
                    $tarjetas = $f->items->pluck('tarjeta_id')->filter()->map(fn($tid) => $tarjetasMap->get($tid))->filter()->values();

                    $this->logDebug("Tarjetas para factura {$facturaCode}", [
                        'count' => $tarjetas->count(),
                        'tarjetas' => $tarjetas->pluck('numero')->toArray(),
                    ]);

                    // Valor total: suma de tarjetas o total de la factura
                    $sumTarjetas = (float) $tarjetas->sum('valor');
                    $valorTotal = $sumTarjetas > 0 ? $sumTarjetas : (float) $f->total;

                    $detalleResponses = [];
                    $anyError = false;

                    // ====== Helpers para interpretar respuestas del servicio ======
                    $toArray = function ($resp) {
                        if (is_array($resp)) {
                            return $resp;
                        }
                        if (is_object($resp)) {
                            return json_decode(json_encode($resp), true);
                        }
                        return ['raw' => $resp];
                    };
                    $isFail = function (array $r) {
                        if (array_key_exists('ok', $r) && $r['ok'] === false) {
                            return true;
                        }
                        if (isset($r['status']) && (int) $r['status'] >= 400) {
                            return true;
                        }
                        if (!empty($r['error'])) {
                            return true;
                        }
                        if (!empty($r['data']['error']['message'])) {
                            return true;
                        }
                        return false;
                    };
                    $getMsg = function (array $r) {
                        return $r['error'] ?? ($r['message'] ?? ($r['data']['error']['message'] ?? ($r['data']['message'] ?? 'Error desconocido')));
                    };

                    // ====== 2.1) Líneas DETALLE (negativas), una por tarjeta ======
                    $detalleResponses = [];
                    $anyError = false;

                    foreach ($tarjetas as $t) {
                        $payloadDetalle = [
                            'Diario' => $diario,
                            'Seccion' => $seccion,
                            'TerceroTipo' => 'G/L Account',
                            'NumeroCuenta' => $glCuentaDetalle,
                            'Description' => "TarjetaBR N {$t->numero} (Fac {$facturaCode})",
                            'TipoDocumento' => $tipoDocumento,
                            'Factura' => $facturaCode,
                            'Fecha_Registro' => $fechaLinea,
                            'Importe' => -1 * (float) $t->valor,
                            'Tercero' => (string) $tercero,
                            'LibroCodigo' => $libroCodigo,
                            'CodigoConcepto' => $codigoConcepto,
                            'GrupoImpuesto' => $grupoImpuesto,
                            'AreaImpuesto' => $areaImpuesto,
                            'DocumentoExterno' => $facturaCode,
                        ];

                        try {
                            $this->logDebug("Enviando línea detalle a BC", [
                                'factura' => $facturaCode,
                                'tarjeta' => $t->numero,
                                'importe' => $payloadDetalle['Importe'],
                            ]);

                            $resp = $bc->createCustomJournalLine($companyId, $payloadDetalle);
                            $arr = $toArray($resp);

                            if ($isFail($arr)) {
                                $errorMsg = $getMsg($arr);
                                $this->logError("Error en línea detalle", [
                                    'factura' => $facturaCode,
                                    'tarjeta' => $t->numero,
                                    'error' => $errorMsg,
                                    'response' => $arr,
                                    'payload' => $payloadDetalle,
                                ]);
                                $anyError = true;
                                $detalleResponses[] = [
                                    'ok' => false,
                                    'payload' => $payloadDetalle,
                                    'error' => $errorMsg,
                                    'resp' => $arr,
                                ];
                                $errorLog[] = [
                                    'factura_id' => $f->id,
                                    'numero_factura' => $facturaCode,
                                    'tarjeta' => $t->numero,
                                    'error' => "Detalle: $errorMsg",
                                ];
                                $errorCount++;
                            } else {
                                $this->logDebug("Línea detalle exitosa", [
                                    'factura' => $facturaCode,
                                    'tarjeta' => $t->numero,
                                    'response' => $arr,
                                ]);
                                $detalleResponses[] = [
                                    'ok' => true,
                                    'payload' => $payloadDetalle,
                                    'resp' => $arr,
                                ];
                            }
                        } catch (\Throwable $e) {
                            $this->logError("Excepción en línea detalle", [
                                'factura' => $facturaCode,
                                'tarjeta' => $t->numero,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);
                            $anyError = true;
                            $detalleResponses[] = [
                                'ok' => false,
                                'payload' => $payloadDetalle,
                                'error' => $e->getMessage(),
                            ];
                            $errorLog[] = [
                                'factura_id' => $f->id,
                                'numero_factura' => $facturaCode,
                                'tarjeta' => $t->numero,
                                'error' => "Excepción: " . $e->getMessage(),
                            ];
                            $errorCount++;
                        }
                    }

                    // ====== 2.2) Línea TOTAL (positiva), una por factura ======
                    $numsTarjetas = $tarjetas->pluck('numero')->filter()->values();
                    $descTotal = "Fac {$facturaCode}";
                    if ($numsTarjetas->isNotEmpty()) {
                        $descTotal .= '(T: ' . $numsTarjetas->implode(', ') . ')';
                    }
                    $descTotal = mb_strimwidth($descTotal, 0, 100, '…');

                    $metodos = Payment::query()->leftJoin('payment_methods as pm', 'pm.id', '=', 'payments.method_id')
                        ->where('payments.invoice_id', $f->id)
                        ->pluck('pm.tipo')
                        ->filter()
                        ->unique()
                        ->values();

                    if ($metodos->isEmpty()) {
                        $metodoPago = '';
                    } elseif ($metodos->count() > 1) {
                        $metodoPago = 'MULMEDPAG';
                    } else {
                        $metodoPago = $metodos->first();
                    }

                    $payloadTotal = [
                        'Diario' => $diario,
                        'Seccion' => $seccion,
                        'TerceroTipo' => 'Bank Account',
                        'NumeroCuenta' => $bankAccountNo,
                        'Description' => $descTotal,
                        'TipoDocumento' => $tipoDocumento,
                        'Factura' => $facturaCode,
                        'Fecha_Registro' => $fechaLinea,
                        'Importe' => (float) $valorTotal,
                        'Tercero' => (string) $tercero,
                        'LibroCodigo' => $libroCodigo,
                        'CodigoConcepto' => $codigoConcepto,
                        'GrupoImpuesto' => $grupoImpuesto,
                        'AreaImpuesto' => $areaImpuesto,
                        'CodigoAuditoria' => $metodoPago,
                        'DocumentoExterno' => $facturaCode,
                    ];

                    $totalResponse = null;
                    try {
                        $this->logDebug("Enviando línea total a BC", [
                            'factura' => $facturaCode,
                            'importe' => $payloadTotal['Importe'],
                            'metodo_pago' => $metodoPago,
                        ]);

                        $resp = $bc->createCustomJournalLine($companyId, $payloadTotal);
                        $arr = $toArray($resp);

                        if ($isFail($arr)) {
                            $errorMsg = $getMsg($arr);
                            $this->logError("Error en línea total", [
                                'factura' => $facturaCode,
                                'error' => $errorMsg,
                                'response' => $arr,
                                'payload' => $payloadTotal,
                            ]);
                            $anyError = true;
                            $totalResponse = [
                                'ok' => false,
                                'payload' => $payloadTotal,
                                'error' => $errorMsg,
                                'resp' => $arr,
                            ];
                            $errorLog[] = [
                                'factura_id' => $f->id,
                                'numero_factura' => $facturaCode,
                                'error' => "Total: $errorMsg",
                            ];
                            $errorCount++;
                        } else {
                            $this->logDebug("Línea total exitosa", [
                                'factura' => $facturaCode,
                                'response' => $arr,
                            ]);
                            $totalResponse = [
                                'ok' => true,
                                'payload' => $payloadTotal,
                                'resp' => $arr,
                            ];
                        }
                    } catch (\Throwable $e) {
                        $this->logError("Excepción en línea total", [
                            'factura' => $facturaCode,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $anyError = true;
                        $totalResponse = [
                            'ok' => false,
                            'payload' => $payloadTotal,
                            'error' => $e->getMessage(),
                        ];
                        $errorLog[] = [
                            'factura_id' => $f->id,
                            'numero_factura' => $facturaCode,
                            'error' => "Excepción total: " . $e->getMessage(),
                        ];
                        $errorCount++;
                    }

                    // ====== 2.3) Actualiza estado_bc e intentos ======
                    $errores = [];
                    foreach ($detalleResponses as $dr) {
                        if (isset($dr['ok']) && $dr['ok'] === false) {
                            $errores[] = $dr['error'] ?? 'Error en línea de detalle';
                        }
                    }
                    if (is_array($totalResponse) && isset($totalResponse['ok']) && $totalResponse['ok'] === false) {
                        $errores[] = $totalResponse['error'] ?? 'Error en línea total';
                    }
                    $errores = array_values(array_unique(array_filter($errores)));
                    $erroresTxt = $errores ? mb_strimwidth(implode(' | ', $errores), 0, 500, '…') : null;

                    try {
                        $f->bc_intentos = (int) $f->bc_intentos + 1;
                        if ($anyError) {
                            $f->estado_bc = 'ERROR';
                            $f->bc_ultimo_error = $erroresTxt ?: 'Error en líneas';
                            $this->logWarning("Factura marcada como ERROR", [
                                'factura_id' => $f->id,
                                'factura_code' => $facturaCode,
                                'errores' => $erroresTxt,
                            ]);
                        } else {
                            $f->estado_bc = 'ENVIADO';
                            $f->bc_ultimo_error = null;
                            $successCount++;
                            $this->logInfo("Factura enviada exitosamente", [
                                'factura_id' => $f->id,
                                'factura_code' => $facturaCode,
                            ]);
                        }
                        $f->save();
                    } catch (\Throwable $e) {
                        $this->logError("No se pudo actualizar estado_bc", [
                            'factura_id' => $f->id,
                            'factura_code' => $facturaCode,
                            'error' => $e->getMessage(),
                        ]);
                        $results[] = [
                            'factura_id' => $f->id,
                            'numero_factura' => $facturaCode,
                            'warning' => 'No se pudo actualizar estado_bc: ' . $e->getMessage(),
                        ];
                    }

                    $results[] = [
                        'factura_id' => $f->id,
                        'numero_factura' => $facturaCode,
                        'fecha' => $fechaLinea,
                        'cliente_cedula' => $tercero,
                        'tarjetas_count' => $tarjetas->count(),
                        'valor_total' => $valorTotal,
                        'detalle' => $detalleResponses,
                        'total' => $totalResponse,
                        'estado_bc' => $f->estado_bc,
                    ];

                } catch (\Throwable $e) {
                    $this->logError("Error procesando factura {$facturaCode}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'factura_id' => $f->id,
                    ]);
                    $errorCount++;
                    $errorLog[] = [
                        'factura_id' => $f->id,
                        'numero_factura' => $facturaCode,
                        'error' => "Procesamiento: " . $e->getMessage(),
                    ];
                }
            }

            // Log de resumen de la operación
            $this->logBulkOperation('carga_bc', [
                'total_facturas' => count($facturas),
                'exitosas' => $successCount,
                'errores' => $errorCount,
                'companyId' => $companyId,
            ], $errorLog);

            return response()->json(
                [
                    'mode' => 'cargar',
                    'companyId' => $companyId,
                    'requested_ids' => $ids,
                    'missing_ids' => $notFound,
                    'processed' => count($results),
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'error_log' => $errorLog,
                    'results' => $results,
                    'preview' => $previewData,
                ],
                201,
            );

        } catch (\Throwable $e) {
            $this->logError('Error crítico en CargarBC', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $req->all(),
            ]);
            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    public function CargarClientes(Request $req, BusinessCentralService $bc)
    {
        try {
            $this->logInfo('INICIO - CargarClientes', [
                'limit' => $req->input('limit', 200),
                'user_id' => auth()->id(),
            ]);

            $limit = (int) $req->input('limit', 200);
            $limit = max(1, min($limit, 1000));

            // Clientes pendientes de cargar (cargado = 0 o NULL)
            $clientes = ClienteBonoR::query()
                ->where(function ($q) {
                    $q->whereNull('cargado')->orWhere('cargado', 0);
                })
                ->orderBy('id')
                ->limit($limit)
                ->get();

            $this->logInfo('Clientes encontrados para procesar', [
                'count' => $clientes->count(),
                'limit' => $limit,
            ]);

            $processed = 0;
            $okBoth = 0;
            $anyErrors = 0;
            $customersOk = 0;
            $customersErr = 0;
            $dimsOk = 0;
            $dimsErr = 0;
            $errorLog = [];

            foreach ($clientes as $c) {
                $processed++;
                $this->logDebug("Procesando cliente ID: {$c->id}", [
                    'cedula' => $c->cedula,
                    'nombre' => $c->nombre,
                    'tipo_documento' => $c->tipo_documento,
                ]);

                try {
                    // Nombre a mostrar (empresa vs persona)
                    $esEmpresa = in_array($c->tipo_documento, ['NIT', 'NIT de otro país'], true);
                    $nombreMostrar = $esEmpresa ? ($c->razons ?: null) : trim(implode(' ', array_filter([$c->nombre, $c->segundo_nombre, $c->apellidos, $c->segundo_apellido])));

                    // 1) Sincroniza cliente en BC
                    $sync = $this->syncCustomerToBC($c, $bc, $nombreMostrar);
                    $okCustomer = (bool) ($sync['ok'] ?? false);
                    $okCustomer ? $customersOk++ : $customersErr++;

                    if (!$okCustomer) {
                        $errorMsg = $sync['error'] ?? 'Error al crear/actualizar cliente en BC';
                        $this->logError("Error syncCustomerToBC", [
                            'cliente_id' => $c->id,
                            'cedula' => $c->cedula,
                            'error' => $errorMsg,
                            'sync_response' => $sync,
                        ]);
                        $errorLog[] = [
                            'cliente_id' => $c->id,
                            'cedula' => $c->cedula,
                            'tipo' => 'customer',
                            'error' => $errorMsg,
                        ];
                    }

                    // 2) Crea/actualiza dimensión en BC
                    $dim = $this->crearDimension($c, $bc, $nombreMostrar);
                    $okDim = (bool) ($dim['ok'] ?? false);
                    $okDim ? $dimsOk++ : $dimsErr++;

                    if (!$okDim) {
                        $errorMsg = $dim['error'] ?? 'Error al crear dimensión en BC';
                        $this->logError("Error crearDimension", [
                            'cliente_id' => $c->id,
                            'cedula' => $c->cedula,
                            'error' => $errorMsg,
                            'dim_response' => $dim,
                        ]);
                        $errorLog[] = [
                            'cliente_id' => $c->id,
                            'cedula' => $c->cedula,
                            'tipo' => 'dimension',
                            'error' => $errorMsg,
                        ];
                    }

                    // 3) Persistencia local
                    try {
                        if ($okCustomer) {
                            $final = $sync['final'] ?? [];
                            $c->bc_system_id = $final['systemId'] ?? ($final['SystemId'] ?? $c->bc_system_id);
                            $c->bc_etag = $final['@odata.etag'] ?? ($final['etag'] ?? $c->bc_etag);
                            $c->bc_synced_at = now();
                            $c->bc_error = null;
                        } else {
                            $err = $sync['error'] ?? 'Error al crear/actualizar cliente en BC';
                            $c->bc_error = mb_strimwidth($err, 0, 500, '…');
                        }

                        // Sólo marcamos "cargado" si cliente y dimensión OK
                        $c->cargado = $okCustomer && $okDim ? 1 : 0;
                        $c->save();

                        if ($c->cargado) {
                            $this->logInfo("Cliente cargado exitosamente", [
                                'cliente_id' => $c->id,
                                'cedula' => $c->cedula,
                            ]);
                        }

                    } catch (\Throwable $e) {
                        $this->logError("Error al guardar cliente en BD", [
                            'cliente_id' => $c->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $errorLog[] = [
                            'cliente_id' => $c->id,
                            'cedula' => $c->cedula,
                            'tipo' => 'database',
                            'error' => "Error al guardar: " . $e->getMessage(),
                        ];
                    }

                    if ($okCustomer && $okDim) {
                        $okBoth++;
                    } else {
                        $anyErrors++;
                    }

                } catch (\Throwable $e) {
                    $this->logError("Error procesando cliente {$c->id}", [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'cliente_id' => $c->id,
                        'cedula' => $c->cedula,
                    ]);
                    $anyErrors++;
                    $errorLog[] = [
                        'cliente_id' => $c->id,
                        'cedula' => $c->cedula,
                        'tipo' => 'exception',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Log de resumen de la operación
            $this->logBulkOperation('carga_clientes', [
                'processed' => $processed,
                'ok_both' => $okBoth,
                'errors' => $anyErrors,
                'customers_ok' => $customersOk,
                'customers_error' => $customersErr,
                'dimensions_ok' => $dimsOk,
                'dimensions_error' => $dimsErr,
            ], $errorLog);

            return response()->json(
                [
                    'mode' => 'cargar_clientes',
                    'processed' => $processed,
                    'ok_both' => $okBoth,
                    'errors' => $anyErrors,
                    'customers_ok' => $customersOk,
                    'customers_error' => $customersErr,
                    'dimensions_ok' => $dimsOk,
                    'dimensions_error' => $dimsErr,
                    'error_details' => $errorLog,
                ],
                200,
            );

        } catch (\Throwable $e) {
            $this->logError('Error crítico en CargarClientes', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $req->all(),
            ]);
            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    private function syncCustomerToBC(ClienteBonoR $c, BusinessCentralService $bc, ?string $nombreMostrar = null): array
    {
        try {
            $this->logDebug("Sincronizando cliente a BC", [
                'cliente_id' => $c->id,
                'cedula' => $c->cedula,
            ]);

            $companyId = env('BC_COMPANY_ID');
            if (!$companyId) {
                $this->logError("Falta BC_COMPANY_ID", ['cliente_id' => $c->id]);
                return ['ok' => false, 'error' => 'Falta BC_COMPANY_ID'];
            }

            $esEmpresa = in_array($c->tipo_documento, ['NIT', 'NIT de otro país'], true);

            $tipoIdentificacion = $c->tipo_documento;
            $tipoContribuyente = $esEmpresa ? 'Persona Jurídica' : 'Persona Natural';
            $areaImpuesto = $c->tipoActividad;
            $correo = strtolower($c->correo);

            if (!$nombreMostrar) {
                $nombreMostrar = $esEmpresa ? $c->razons : trim(implode(' ', array_filter([$c->nombre, $c->segundo_nombre, $c->apellidos, $c->segundo_apellido])));
            }

            // CREATE (sin NumeroIdentificacion)
            $payloadCreate = array_filter(
                [
                    'No' => $c->cedula,
                    'NombreCompleto' => $nombreMostrar ?: null,
                    'Correo' => $correo ?: null,
                    'CorreoFE' => $correo ?: null,
                    'Tipoidentificacion' => $tipoIdentificacion ?: null,
                    'TipoContribuyente' => $tipoContribuyente,
                    'AreaImpuesto' => $areaImpuesto,
                    'PrimerNombre' => $esEmpresa ? null : ($c->nombre ?: null),
                    'SegundoNombre' => $esEmpresa ? null : ($c->segundo_nombre ?: null),
                    'PrimerApellido' => $esEmpresa ? null : ($c->apellidos ?: null),
                    'SegundoApellido' => $esEmpresa ? null : ($c->segundo_apellido ?: null),
                    'RazonSocial' => $esEmpresa ? ($c->razons ?: null) : null,
                ],
                fn($v) => !(is_string($v) && trim($v) === '') && $v !== null,
            );

            try {
                $created = $bc->createCustomerExt($companyId, $payloadCreate);
            } catch (\Throwable $e) {
                $this->logError("Error en createCustomerExt", [
                    'cliente_id' => $c->id,
                    'error' => $e->getMessage(),
                    'payload' => $payloadCreate,
                ]);
                return ['ok' => false, 'error' => 'createCustomerExt: ' . $e->getMessage()];
            }

            $systemId = $created['systemId'] ?? ($created['SystemId'] ?? null);

            if (!$systemId) {
                $this->logError("No se obtuvo systemId al crear cliente", [
                    'cliente_id' => $c->id,
                    'response' => $created,
                ]);
                return ['ok' => false, 'error' => 'No se obtuvo systemId', 'response' => $created];
            }

            // PATCH (NumeroIdentificacion)
            $patched = null;
            if (!empty($c->cedula)) {
                try {
                    $patched = $bc->updateCustomerExt($companyId, $systemId, [
                        'NumeroIdentificacion' => (string) $c->cedula,
                    ]);
                } catch (\Throwable $e) {
                    $this->logError("Error en updateCustomerExt", [
                        'cliente_id' => $c->id,
                        'systemId' => $systemId,
                        'error' => $e->getMessage(),
                    ]);
                    // No marcamos como error completo, solo logueamos
                }
            }

            $final = $patched ?: $created ?: [];

            $this->logDebug("Cliente sincronizado exitosamente", [
                'cliente_id' => $c->id,
                'systemId' => $systemId,
            ]);

            return [
                'ok' => true,
                'final' => $final,
                'created' => $created,
                'patched' => $patched,
            ];

        } catch (\Throwable $e) {
            $this->logError("Error en syncCustomerToBC", [
                'cliente_id' => $c->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function crearDimension(ClienteBonoR $c, BusinessCentralService $bc, ?string $nombreMostrar = null): array
    {
        try {
            $this->logDebug("Creando dimensión en BC", [
                'cliente_id' => $c->id,
                'cedula' => $c->cedula,
            ]);

            $companyId = env('BC_COMPANY_ID');
            $dimensionCode = env('BC_DIM_TERCERO', 'TERCERO');

            if (!$companyId) {
                $this->logError("Falta BC_COMPANY_ID para dimensión", ['cliente_id' => $c->id]);
                return ['ok' => false, 'error' => 'Falta BC_COMPANY_ID'];
            }

            $code = (string) ($c->cedula ?: $c->bc_no ?: $c->id);

            if ($nombreMostrar === null || trim($nombreMostrar) === '') {
                $nombreMostrar = $c->razon_social ?: trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([$c->nombre, $c->segundo_nombre, $c->apellidos, $c->segundo_apellido]))));
            }

            $payload = [
                'DimensionCode' => $dimensionCode,
                'Code' => mb_substr($code, 0, 50),
                'Name' => mb_substr((string) $nombreMostrar, 0, 100),
            ];

            $payload = array_filter($payload, fn($v) => !((is_string($v) && trim($v) === '') || $v === null));

            try {
                $resp = $bc->upsertDimensionValue($companyId, $payload);
                $this->logDebug("Dimensión creada exitosamente", [
                    'cliente_id' => $c->id,
                    'code' => $payload['Code'],
                ]);
                return ['ok' => true, 'payload' => $payload, 'response' => $resp];
            } catch (\Throwable $e) {
                $this->logError("Error en upsertDimensionValue", [
                    'cliente_id' => $c->id,
                    'error' => $e->getMessage(),
                    'payload' => $payload,
                ]);
                return ['ok' => false, 'error' => 'upsertDimensionValue: ' . $e->getMessage()];
            }

        } catch (\Throwable $e) {
            $this->logError("Error en crearDimension", [
                'cliente_id' => $c->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}