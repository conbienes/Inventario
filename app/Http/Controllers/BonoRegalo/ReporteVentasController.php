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

class ReporteVentasController extends Controller
{
    public function index(Request $request)
    {
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
    }

    // ==== EXPORT A EXCEL ====
    public function exportExcel(Request $request)
    {
        // composer require maatwebsite/excel:^3.1
        // php artisan make:export VentasExport --model=Payment
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        return \Excel::download(new \App\Exports\VentasExport($from, $to), "ventas_{$from}_{$to}.xlsx");
    }

    // ==== EXPORT A PDF ====
    public function exportPdf(Request $request)
    {
        // composer require barryvdh/laravel-dompdf:^2.0
        // php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $statusOk = ['approved', 'paid', 'success', 'completed'];
        $data = Payment::with('method', 'invoice')
            ->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
            ->whereIn('status', $statusOk)
            ->get();

        $pdf = \PDF::loadView('bono-regalo.exports.ventas-pdf', [
            'from' => $from,
            'to' => $to,
            'rows' => $data,
        ])->setPaper([0, 0, 612, 792], 'portrait'); // carta

        return $pdf->download("ventas_{$from}_{$to}.pdf");
    }

    public function Contabilidad(Request $request)
    {
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

        return view('BonoRegalo.Contabilidad.Index', compact('facturas', 'estados', 'allEstados', 'desde', 'hasta', 'q'));
    }

    public function CargarBC(Request $req, BusinessCentralService $bc)
    {
        // ========= 1) PRIMERA FASE: sacar info (preview) =========
        $ids = (array) $req->input('ids', []);

        // Normaliza y valida IDs
        $ids = array_values(array_unique(array_filter($ids, fn($v) => is_numeric($v))));
        if (empty($ids)) {
            return response()->json(['message' => 'Debe enviar ids[] de facturas.'], 422);
        }
        $ids = array_map('intval', $ids);

        // Trae facturas con cliente e ítems (para leer tarjeta_id)
        $facturas = FacturaBonoR::query()
            ->with(['cliente:id,tipo_documento,cedula,nombre,segundo_nombre,apellidos,segundo_apellido,correo,razons', 'items:id,factura_id,tarjeta_id'])
            ->whereIn('id', $ids)
            ->orderByDesc('fecha')
            ->get();

        // IDs no encontrados
        $notFound = array_values(array_diff($ids, $facturas->pluck('id')->all()));

        // Prefetch de todas las tarjetas referenciadas por los ítems
        $tarjetaIds = $facturas->flatMap(fn($f) => $f->items->pluck('tarjeta_id'))->filter()->unique()->values();
        $tarjetasMap = TarjetaBonoR::whereIn('id', $tarjetaIds)
            ->get(['id', 'numero', 'nit', 'valor', 'estado'])
            ->keyBy('id');

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
            return response()->json(['error' => 'Falta companyId'], 400);
        }

        // Parámetros BC (permiten override por request)
        $tipoDocumento = $req->input('tipoDocumento', 'Invoice');

        $glCuentaDetalle = $req->input('ctaDetalle', '11051002'); // G/L Account (detalle, negativa)
        $bankAccountNo = $req->input('ctaTotal', 'BANCOLOMBIA 6289 BR'); // Bank Account No. (total, positiva)

        $libroCodigo = $req->input('libroCodigo', '1_NIIF');
        $codigoConcepto = $req->input('codigoConcepto', '110409');
        $grupoImpuesto = $req->input('grupoImpuesto', 'G COMPR 0%_');
        $areaImpuesto = $req->input('areaImpuesto', 'CLI-NRI');
        $diario = $req->input('diario', 'BONOREGALO');
        $seccion = $req->input('seccion', 'BONO REG');

        $results = [];

        foreach ($facturas as $f) {
            $facturaCode = $f->numero_factura ?: 'BR' . $f->id;
            // Fecha: usa la enviada o la de la factura
            $fechaLinea = $req->input('fecha', $f->fecha ? \Carbon\Carbon::parse($f->fecha)->toDateString() : \Carbon\Carbon::now()->toDateString());
            // Tercero: preferimos cedula del cliente; si no, puede venir en request
            $tercero = $f->cliente->cedula ?? (string) $req->input('tercero', '');

            if ($tercero === '') {
                $results[] = [
                    'factura_id' => $f->id,
                    'numero_factura' => $facturaCode,
                    'error' => 'Factura sin cliente.cedula y no se envió "tercero" en la petición',
                ];
                continue;
            }

            // Tarjetas asociadas
            $tarjetas = $f->items->pluck('tarjeta_id')->filter()->map(fn($tid) => $tarjetasMap->get($tid))->filter()->values();

            // Valor total: suma de tarjetas o total de la factura
            $sumTarjetas = (float) $tarjetas->sum('valor');
            $valorTotal = $sumTarjetas > 0 ? $sumTarjetas : (float) $f->total;

            $detalleResponses = [];
            $anyError = false;

            // 2.1) Líneas DETALLE (negativas), una por tarjeta
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
                    'Importe' => -1 * (float) $t->valor, // NEGATIVO
                    'Tercero' => (string) $tercero,
                    'LibroCodigo' => $libroCodigo,
                    'CodigoConcepto' => $codigoConcepto,
                    'GrupoImpuesto' => $grupoImpuesto,
                    'AreaImpuesto' => $areaImpuesto,
                    'DocumentoExterno'=> $facturaCode, // Código de la factura
                ];

                try {
                    $resp = $bc->createCustomJournalLine($companyId, $payloadDetalle);
                    $arr = $toArray($resp);

                    if ($isFail($arr)) {
                        $anyError = true;
                        $detalleResponses[] = [
                            'ok' => false,
                            'payload' => $payloadDetalle,
                            'error' => $getMsg($arr),
                            'resp' => $arr,
                        ];
                    } else {
                        $detalleResponses[] = [
                            'ok' => true,
                            'payload' => $payloadDetalle,
                            'resp' => $arr,
                        ];
                    }
                } catch (\Throwable $e) {
                    $anyError = true;
                    $detalleResponses[] = [
                        'ok' => false,
                        'payload' => $payloadDetalle,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // ====== 2.2) Línea TOTAL (positiva), una por factura ======
            $numsTarjetas = $tarjetas->pluck('numero')->filter()->values();
            $descTotal = "Fac {$facturaCode}";
            if ($numsTarjetas->isNotEmpty()) {
                $descTotal .= '(T: ' . $numsTarjetas->implode(', ') . ')';
            }
            // (opcional) limitar longitud si BC tiene tope de caracteres
            $descTotal = mb_strimwidth($descTotal, 0, 100, '…');

            $metodos = Payment::query()->leftJoin('payment_methods as pm', 'pm.id', '=', 'payments.method_id')->where('payments.invoice_id', $f->id)->pluck('pm.tipo')->filter()->unique()->values();

            if ($metodos->isEmpty()) {
                $metodoPago = '';
            } elseif ($metodos->count() > 1) {
                $metodoPago = 'MULMEDPAG'; // varios métodos → MULMEDPAG
            } else {
                $metodoPago = $metodos->first(); // solo uno → el nombre
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
                'Importe' => (float) $valorTotal, // POSITIVO
                'Tercero' => (string) $tercero,
                'LibroCodigo' => $libroCodigo,
                'CodigoConcepto' => $codigoConcepto,
                'GrupoImpuesto' => $grupoImpuesto,
                'AreaImpuesto' => $areaImpuesto,
                'CodigoAuditoria'=> $metodoPago, // método de pago o MULMEDPAG
                'DocumentoExterno'=> $facturaCode, //
            ];

            $totalResponse = null;
            try {
                $resp = $bc->createCustomJournalLine($companyId, $payloadTotal);
                $arr = $toArray($resp);

                if ($isFail($arr)) {
                    $anyError = true;
                    $totalResponse = [
                        'ok' => false,
                        'payload' => $payloadTotal,
                        'error' => $getMsg($arr),
                        'resp' => $arr,
                    ];
                } else {
                    $totalResponse = [
                        'ok' => true,
                        'payload' => $payloadTotal,
                        'resp' => $arr,
                    ];
                }
            } catch (\Throwable $e) {
                $anyError = true;
                $totalResponse = [
                    'ok' => false,
                    'payload' => $payloadTotal,
                    'error' => $e->getMessage(),
                ];
            }

            // ====== 2.3) Actualiza estado_bc e intentos (considerando errores de detalle y total) ======
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
                } else {
                    $f->estado_bc = 'ENVIADO';
                    $f->bc_ultimo_error = null;
                }
                $f->save();
            } catch (\Throwable $e) {
                // reporta pero no detiene
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
        }

        return response()->json(
            [
                'mode' => 'cargar',
                'companyId' => $companyId,
                'requested_ids' => $ids,
                'missing_ids' => $notFound,
                'processed' => count($results),
                'results' => $results,
                'preview' => $previewData, // te dejo también el preview para referencia
            ],
            201,
        );
    }

    public function CargarClientes(Request $req, BusinessCentralService $bc)
    {
        $limit = (int) $req->input('limit', 200);
        $limit = max(1, min($limit, 1000)); // guarda un límite sano

        // Clientes pendientes de cargar (cargado = 0 o NULL)
        $clientes = ClienteBonoR::query()
            ->where(function ($q) {
                $q->whereNull('cargado')->orWhere('cargado', 0);
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $processed = 0;
        $okBoth = 0; // ambos OK
        $anyErrors = 0; // al menos una de las dos falló
        $customersOk = 0; // cliente OK
        $customersErr = 0; // cliente error
        $dimsOk = 0; // dimensión OK
        $dimsErr = 0; // dimensión error

        // $results = []; // si luego quieres detalle por cliente, descomenta y rellena

        foreach ($clientes as $c) {
            $processed++;

            // Nombre a mostrar (empresa vs persona)
            $esEmpresa = in_array($c->tipo_documento, ['NIT', 'NIT de otro país'], true);
            $nombreMostrar = $esEmpresa ? ($c->razons ?: null) : trim(implode(' ', array_filter([$c->nombre, $c->segundo_nombre, $c->apellidos, $c->segundo_apellido])));

            // 1) Sincroniza cliente en BC
            $sync = $this->syncCustomerToBC($c, $bc, $nombreMostrar);
            $okCustomer = (bool) ($sync['ok'] ?? false);
            $okCustomer ? $customersOk++ : $customersErr++;

            // 2) Crea/actualiza dimensión en BC (TERCERO, etc.)
            $dim = $this->crearDimension($c, $bc, $nombreMostrar);
            $okDim = (bool) ($dim['ok'] ?? false);
            $okDim ? $dimsOk++ : $dimsErr++;

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
                    $c->bc_error = mb_strimwidth($err, 0, 500, '…'); // evita overflow en DB
                }

                // Sólo marcamos “cargado” si cliente y dimensión OK
                $c->cargado = $okCustomer && $okDim ? 1 : 0;
                $c->save();
            } catch (\Throwable $e) {
                // si falla el save local, lo ignoramos para el resumen
            }

            if ($okCustomer && $okDim) {
                $okBoth++;
            } else {
                $anyErrors++;
            }

            // $results[] = [
            //     'cliente_id' => $c->id,
            //     'cedula' => $c->cedula,
            //     'ok_customer' => $okCustomer,
            //     'ok_dimension' => $okDim,
            //     'error_customer' => $okCustomer ? null : ($sync['error'] ?? null),
            //     'error_dimension' => $okDim ? null : ($dim['error'] ?? null),
            //     'cargado' => (int) $c->cargado,
            // ];
        }

        return response()->json(
            [
                'mode' => 'cargar_clientes',
                'processed' => $processed,
                'ok_both' => $okBoth, // clientes con ambas operaciones OK
                'errors' => $anyErrors, // clientes con algún error
                'customers_ok' => $customersOk,
                'customers_error' => $customersErr,
                'dimensions_ok' => $dimsOk,
                'dimensions_error' => $dimsErr,
                // 'results' => $results, // si quieres devolver detalle, descomenta
            ],
            200,
        );
    }

    private function syncCustomerToBC(ClienteBonoR $c, BusinessCentralService $bc, ?string $nombreMostrar = null): array
    {
        try {
            $companyId = env('BC_COMPANY_ID');
            if (!$companyId) {
                return ['ok' => false, 'error' => 'Falta BC_COMPANY_ID'];
            }

            $esEmpresa = in_array($c->tipo_documento, ['NIT', 'NIT de otro país'], true);

            $tipoIdentificacion = $c->tipo_documento; // usa tus rótulos/códigos
            $tipoContribuyente = $esEmpresa ? 'Persona Jurídica' : 'Persona Natural';
            $areaImpuesto = $c->tipoActividad; // "CLI-NRI" / "CLI-RI"
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

            $created = $bc->createCustomerExt($companyId, $payloadCreate);

            $systemId = $created['systemId'] ?? ($created['SystemId'] ?? null);

            // PATCH (NumeroIdentificacion)
            $patched = null;
            $okCreate = (bool) $systemId;
            $okPatch = true;

            if ($okCreate && !empty($c->cedula)) {
                $patched = $bc->updateCustomerExt($companyId, $systemId, [
                    'NumeroIdentificacion' => (string) $c->cedula,
                ]);
            }
            // Tomamos el objeto “final” para persistir (si hubo PATCH, preferimos patched)
            $final = $patched ?: $created ?: [];

            // Notar que el JSON trae "@odata.etag" y "lastModified"
            $ok = $okCreate && ($patched !== null ? true : true); // si tu servicio lanza excepción, ya cae al catch

            return [
                'ok' => $ok,
                'final' => $final, // <-- lo usaremos para mapear y guardar
                'created' => $created,
                'patched' => $patched,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function crearDimension(ClienteBonoR $c, BusinessCentralService $bc, ?string $nombreMostrar = null): array
    {
        try {
            // Lee de .env (con defaults sensatos)
            $companyId = env('BC_COMPANY_ID');
            $dimensionCode = env('BC_DIM_TERCERO', 'TERCERO');

            if (!$companyId) {
                return ['ok' => false, 'error' => 'Falta BC_COMPANY_ID'];
            }

            // Code: cédula -> bc_no -> id local
            $code = (string) ($c->cedula ?: $c->bc_no ?: $c->id);

            // Name: si no viene, lo componemos desde el modelo
            if ($nombreMostrar === null || trim($nombreMostrar) === '') {
                $nombreMostrar = $c->razon_social ?: trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([$c->nombre, $c->segundo_nombre, $c->apellidos, $c->segundo_apellido]))));
            }

            // Recortes de seguridad (ajusta si tu API limita distinto)
            $payload = [
                'DimensionCode' => $dimensionCode, // <--- usa el de .env
                'Code' => mb_substr($code, 0, 50),
                'Name' => mb_substr((string) $nombreMostrar, 0, 100),
            ];

            // Limpia nulls/cadenas vacías
            $payload = array_filter($payload, fn($v) => !((is_string($v) && trim($v) === '') || $v === null));

            // UPSERT en BC
            $resp = $bc->upsertDimensionValue($companyId, $payload);

            // (Opcional) guardar algo en BD:
            // $c->update(['bc_dim_code'=>$payload['Code'],'bc_dim_name'=>$payload['Name'],'bc_dim_synced_at'=>now()]);

            return ['ok' => true, 'payload' => $payload, 'response' => $resp];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
