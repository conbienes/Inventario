<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\BonoRegalo\TarjetaBonoR;
use Carbon\Carbon;
use App\Exports\TarjetasBRExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TarjetasBRImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TarjetasBRController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (session('modulo_seleccionado') != 3) {
                abort(403, 'Acceso no autorizado para este módulo');
            }
            return $next($request);
        });
    }

    public function index()
    {
        return view('BonoRegalo.Tarjetas.index');
    }

    public function BuscarTarjeta(Request $request)
    {
        $query = TarjetaBonoR::query();

        if ($request->filled('numero')) {
            $query->where('numero', 'like', "%{$request->numero}%");
        }

        if ($request->filled('cedula')) {
            $query->where('nit', 'like', "%{$request->cedula}%");
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->fecha_inicio)->startOfDay(), 
                Carbon::parse($request->fecha_fin)->endOfDay()
            ]);
        } elseif ($request->filled('fecha_inicio')) {
            $query->where('created_at', '>=', Carbon::parse($request->fecha_inicio)->startOfDay());
        } elseif ($request->filled('fecha_fin')) {
            $query->where('created_at', '<=', Carbon::parse($request->fecha_fin)->endOfDay());
        }

        if (!$request->filled('numero') && !$request->filled('cedula') && 
            !$request->filled('fecha_inicio') && !$request->filled('fecha_fin')) {
            $query->whereDate('created_at', now()->toDateString());
        }

        $validSorts = ['id', 'nit', 'numero', 'valor', 'created_at', 'estado'];
        $sort = in_array($request->get('sort'), $validSorts) ? $request->get('sort') : 'created_at';
        $direction = $request->get('direction', 'desc');

        $query->orderBy($sort, $direction);

        $Tarjetas = $query->paginate(15)->appends($request->all());

        $stats = [
            'total' => TarjetaBonoR::count(),
            'activas' => TarjetaBonoR::where('estado', 'activa')->count(),
            'inactivas' => TarjetaBonoR::where('estado', 'inactiva')->count(),
            'valor_total' => TarjetaBonoR::sum('valor'),
        ];

        return view('BonoRegalo.Tarjetas.BuscarTarjetas', compact('Tarjetas', 'stats'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
                'numero' => ['required', 'numeric', 'digits:16', 'unique:tarjetasBonoR,numero'],
                'nit' => 'required|string|max:20',
                'valor' => 'required|numeric|min:0',
                'estado' => 'required|in:activa,inactiva',
            ],
            [
                'numero.required' => 'El número de tarjeta es obligatorio.',
                'numero.numeric' => 'El número de tarjeta debe contener solo números.',
                'numero.digits' => 'El número de tarjeta debe tener exactamente 16 dígitos.',
                'numero.unique' => 'Este número de tarjeta ya está registrado.',
                'valor.numeric' => 'El valor debe ser un número válido.',
                'valor.min' => 'El valor no puede ser negativo.',
                'estado.in' => 'El estado debe ser "activa" o "inactiva".',
            ],
        );

        try {
            DB::beginTransaction();
            $tarjeta = TarjetaBonoR::create($validatedData);
            DB::commit();

            return redirect()->route('BonoRegalo.BuscarTarjeta')
                ->with('success', '✅ Tarjeta #' . $tarjeta->numero . ' registrada correctamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al guardar tarjeta: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['error' => '❌ Error al guardar la tarjeta: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(string $id)
    {
        $tarjeta = TarjetaBonoR::findOrFail($id);
        return view('BonoRegalo.Tarjetas.editarTarjeta', compact('tarjeta'));
    }

    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate(
            [
                'numero' => ['required', 'numeric', 'digits:16', Rule::unique('tarjetasBonoR', 'numero')->ignore($id)],
                'nit' => 'required|string|max:20',
                'valor' => 'required|numeric|min:0',
                'estado' => 'required|in:activa,inactiva',
            ],
            [
                'numero.required' => 'El número de tarjeta es obligatorio.',
                'numero.numeric' => 'El número de tarjeta debe contener solo números.',
                'numero.digits' => 'El número de tarjeta debe tener exactamente 16 dígitos.',
                'numero.unique' => 'Este número de tarjeta ya está registrado.',
                'valor.numeric' => 'El valor debe ser un número válido.',
                'valor.min' => 'El valor no puede ser negativo.',
                'estado.in' => 'El estado debe ser "activa" o "inactiva".',
            ],
        );

        try {
            DB::beginTransaction();
            $tarjeta = TarjetaBonoR::findOrFail($id);
            $tarjeta->update($validatedData);
            DB::commit();

            return redirect()->route('BonoRegalo.BuscarTarjeta')
                ->with('success', '✅ Tarjeta #' . $tarjeta->numero . ' actualizada correctamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar tarjeta: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['error' => '❌ Error al actualizar la tarjeta: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function exportarTarjetas(Request $request)
    {
        try {
            return Excel::download(new TarjetasBRExport($request), 'tarjetas_bono_' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Error al exportar tarjetas: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => '❌ Error al exportar: ' . $e->getMessage()]);
        }
    }

    public function importarTarjetas(Request $request)
    {
        // ============================================
        // 🔥 SISTEMA DE LOGS FORZADO
        // ============================================
        $logFile = storage_path('logs/importacion_' . date('Y-m-d') . '.log');
        
        $writeLog = function($message, $data = null) use ($logFile) {
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[$timestamp] ";
            
            if (is_array($message) || is_object($message)) {
                $logMessage .= print_r($message, true);
            } else {
                $logMessage .= $message;
            }
            
            if ($data !== null) {
                $logMessage .= "\n" . print_r($data, true);
            }
            
            $logMessage .= "\n" . str_repeat("-", 50) . "\n";
            
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        };
        
        // ============================================
        // REGISTRAR INICIO
        // ============================================
        $writeLog("=========================================");
        $writeLog("🚀 INICIO DE IMPORTACIÓN");
        $writeLog("Fecha: " . date('Y-m-d H:i:s'));
        $writeLog("IP: " . $request->ip());
        $writeLog("URL: " . $request->fullUrl());
        $writeLog("Método: " . $request->method());
        $writeLog("=========================================");
        
        // Intentar log de Laravel
        try {
            Log::emergency('🚨 INICIO IMPORTACIÓN DESDE LARAVEL');
            Log::info('📝 Método ejecutado');
        } catch (\Exception $e) {
            $writeLog("⚠️ Error con Log de Laravel: " . $e->getMessage());
        }
        
        // ============================================
        // PROCESAR IMPORTACIÓN
        // ============================================
        try {
            // Validar archivo
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'archivo' => 'required|file|max:2048',
            ], [
                'archivo.required' => 'Debes seleccionar un archivo para importar.',
                'archivo.max' => 'El archivo no debe superar los 2MB.',
            ]);
    
            if ($validator->fails()) {
                $writeLog("❌ Validación falló", $validator->errors()->toArray());
                return redirect()->back()->withErrors($validator)->withInput();
            }
    
            $archivo = $request->file('archivo');
            $extension = strtolower($archivo->getClientOriginalExtension());
            $nombreOriginal = $archivo->getClientOriginalName();
            
            $writeLog("📁 Archivo recibido:", [
                'nombre' => $nombreOriginal,
                'extension' => $extension,
                'tamano' => $archivo->getSize() . ' bytes',
                'mime' => $archivo->getMimeType(),
                'ruta' => $archivo->getRealPath()
            ]);
    
            // Verificar extensión
            if (!in_array($extension, ['csv', 'xlsx', 'xls'])) {
                $writeLog("❌ Extensión no permitida: " . $extension);
                return redirect()->back()
                    ->withErrors(['archivo' => '❌ Solo se permiten archivos: .csv, .xlsx, .xls'])
                    ->withInput();
            }
    
            // ============================================
            // 🔥 LECTURA MANUAL DEL ARCHIVO (para CSV)
            // ============================================
            if ($extension === 'csv') {
                $writeLog("📄 Procesando archivo CSV manualmente");
                
                // Leer contenido del archivo
                $contenido = file_get_contents($archivo->getRealPath());
                $writeLog("📄 CONTENIDO COMPLETO DEL ARCHIVO:", $contenido);
                
                // Dividir por líneas
                $lineas = explode("\n", $contenido);
                $writeLog("📄 ANÁLISIS DEL ARCHIVO:", [
                    'total_lineas' => count($lineas),
                    'lineas_no_vacias' => count(array_filter($lineas, 'trim')),
                    'primera_linea' => isset($lineas[0]) ? trim($lineas[0]) : 'VACÍA',
                    'segunda_linea' => isset($lineas[1]) ? trim($lineas[1]) : 'VACÍA',
                    'tercera_linea' => isset($lineas[2]) ? trim($lineas[2]) : 'VACÍA',
                ]);
                
                // Detectar separador
                $primeraLinea = isset($lineas[0]) ? trim($lineas[0]) : '';
                $separadores = [';', ',', "\t", '|'];
                $separadorEncontrado = ';';
                
                foreach ($separadores as $sep) {
                    if (strpos($primeraLinea, $sep) !== false) {
                        $separadorEncontrado = $sep;
                        break;
                    }
                }
                
                $writeLog("🔍 Separador detectado: '" . $separadorEncontrado . "'");
                
                // Iniciar transacción
                DB::beginTransaction();
                $writeLog("🔄 Transacción iniciada");
                
                // Procesar líneas manualmente
                $importados = 0;
                $errores = [];
                $filasProcesadas = 0;
                
                // Saltar la primera línea (encabezados)
                foreach (array_slice($lineas, 1) as $index => $linea) {
                    $linea = trim($linea);
                    if (empty($linea)) {
                        $writeLog("⚠️ Línea " . ($index + 2) . " vacía, saltando");
                        continue;
                    }
                    
                    $filasProcesadas++;
                    $writeLog("📝 Procesando línea " . ($index + 2) . ":", $linea);
                    
                    // Dividir por el separador detectado
                    $datos = str_getcsv($linea, $separadorEncontrado);
                    
                    // Si no tiene el número correcto de elementos, intentar con otro separador
                    if (count($datos) < 4) {
                        $writeLog("⚠️ Probando con coma como separador");
                        $datos = str_getcsv($linea, ',');
                    }
                    
                    if (count($datos) < 4) {
                        $writeLog("❌ Línea " . ($index + 2) . " tiene " . count($datos) . " elementos, se esperaban 4");
                        $errores[] = "Fila " . ($index + 2) . ": Formato incorrecto - " . $linea;
                        continue;
                    }
                    
                    // Limpiar datos
                    $numero = trim($datos[0]);
                    $nit = trim($datos[1]);
                    $valor = floatval(str_replace(['$', ',', '.'], '', trim($datos[2])));
                    $estado = trim(strtolower($datos[3]));
                    
                    $writeLog("📊 Datos procesados:", [
                        'numero' => $numero,
                        'nit' => $nit,
                        'valor' => $valor,
                        'estado' => $estado
                    ]);
                    
                    // Validar datos
                    if (empty($numero) || strlen($numero) != 16 || !is_numeric($numero)) {
                        $errores[] = "Fila " . ($index + 2) . ": Número inválido - $numero";
                        continue;
                    }
                    
                    if (empty($nit)) {
                        $errores[] = "Fila " . ($index + 2) . ": NIT vacío";
                        continue;
                    }
                    
                    if ($valor < 0) {
                        $errores[] = "Fila " . ($index + 2) . ": Valor inválido - $valor";
                        continue;
                    }
                    
                    if (!in_array($estado, ['activa', 'inactiva'])) {
                        $errores[] = "Fila " . ($index + 2) . ": Estado inválido - $estado (debe ser activa o inactiva)";
                        continue;
                    }
                    
                    // Verificar si la tarjeta ya existe
                    $existe = TarjetaBonoR::where('numero', $numero)->exists();
                    if ($existe) {
                        $errores[] = "Fila " . ($index + 2) . ": Tarjeta $numero ya existe";
                        continue;
                    }
                    
                    try {
                        // Crear la tarjeta
                        TarjetaBonoR::create([
                            'numero' => $numero,
                            'nit' => $nit,
                            'valor' => $valor,
                            'estado' => $estado
                        ]);
                        $importados++;
                        $writeLog("✅ Tarjeta $numero importada correctamente");
                    } catch (\Exception $e) {
                        $errores[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
                        $writeLog("❌ Error al guardar tarjeta $numero:", $e->getMessage());
                    }
                }
                
                DB::commit();
                $writeLog("✅ Transacción completada");
                
                $writeLog("📊 RESULTADOS FINALES:", [
                    'filas_procesadas' => $filasProcesadas,
                    'importados' => $importados,
                    'errores' => count($errores),
                    'lista_errores' => $errores
                ]);
                
                // ============================================
                // MOSTRAR RESULTADOS
                // ============================================
                if ($filasProcesadas == 0) {
                    $mensaje = "⚠️ El archivo fue procesado pero no se encontraron datos.<br>";
                    $mensaje .= "<br><strong>🔍 Posibles causas:</strong>";
                    $mensaje .= "<ul>";
                    $mensaje .= "<li>Los encabezados no coinciden: deben ser <strong>numero, nit, valor, estado</strong></li>";
                    $mensaje .= "<li>El archivo está vacío o solo tiene encabezados</li>";
                    $mensaje .= "<li>El separador no es punto y coma (;) o coma (,) </li>";
                    $mensaje .= "</ul>";
                    $mensaje .= "<br><small>Revisa el log en storage/logs/importacion_" . date('Y-m-d') . ".log para más detalles.</small>";
                    
                    return redirect()->route('BonoRegalo.BuscarTarjeta')
                        ->with('warning', $mensaje);
                }
                
                if (count($errores) > 0) {
                    $mensaje = "❌ Se encontraron <strong>" . count($errores) . "</strong> errores en el archivo.<br>";
                    $mensaje .= "✅ Se importaron <strong>{$importados}</strong> tarjetas correctamente.<br><br>";
                    
                    if (count($errores) > 0) {
                        $detalles = "<strong>🔍 Detalles de errores:</strong><ul class='mb-0 mt-1'>";
                        foreach (array_slice($errores, 0, 10) as $error) {
                            $detalles .= "<li class='small text-danger'>" . htmlspecialchars($error) . "</li>";
                        }
                        if (count($errores) > 10) {
                            $detalles .= "<li class='small text-muted'>... y " . (count($errores) - 10) . " errores más. Revisa el log para detalles completos.</li>";
                        }
                        $detalles .= "</ul>";
                        $mensaje .= $detalles;
                    }
                    
                    $mensaje .= "<br><small>Log completo en: storage/logs/importacion_" . date('Y-m-d') . ".log</small>";
                    
                    return redirect()->route('BonoRegalo.BuscarTarjeta')
                        ->with('error', $mensaje);
                }
                
                if ($importados > 0) {
                    $mensaje = "🎉 ¡Excelente! Se importaron <strong>{$importados}</strong> tarjetas correctamente.";
                    $mensaje .= "<br><small class='text-muted'>Todas las tarjetas fueron procesadas sin errores.</small>";
                    $mensaje .= "<br><small>Log completo en: storage/logs/importacion_" . date('Y-m-d') . ".log</small>";
                    
                    return redirect()->route('BonoRegalo.BuscarTarjeta')
                        ->with('success', $mensaje);
                }
                
                return redirect()->route('BonoRegalo.BuscarTarjeta')
                    ->with('info', 'El archivo no contenía datos para importar.');
                    
            } else {
                // ============================================
                // PROCESAR EXCEL (xlsx, xls)
                // ============================================
                $writeLog("📄 Procesando archivo Excel con Maatwebsite");
                
                DB::beginTransaction();
                $writeLog("🔄 Transacción iniciada");
                
                $import = new TarjetasBRImport();
                $writeLog("📦 Importador creado");
                
                Excel::import($import, $archivo);
                
                DB::commit();
                $writeLog("✅ Transacción completada");
                
                $importados = $import->getImportados();
                $errores = $import->getErrores();
                $erroresLista = $import->getErroresLista();
                $filasProcesadas = $import->getFilasProcesadas();
                
                $writeLog("📊 RESULTADOS FINALES:", [
                    'filas_procesadas' => $filasProcesadas,
                    'importados' => $importados,
                    'errores' => $errores,
                    'lista_errores' => $erroresLista
                ]);
                
                if ($filasProcesadas == 0) {
                    $mensaje = "⚠️ El archivo fue procesado pero no se encontraron datos.<br>";
                    $mensaje .= "<br><strong>🔍 Posibles causas:</strong>";
                    $mensaje .= "<ul>";
                    $mensaje .= "<li>Los encabezados no coinciden: deben ser <strong>numero, nit, valor, estado</strong></li>";
                    $mensaje .= "<li>El archivo está vacío o solo tiene encabezados</li>";
                    $mensaje .= "</ul>";
                    $mensaje .= "<br><small>Revisa el log en storage/logs/importacion_" . date('Y-m-d') . ".log para más detalles.</small>";
                    
                    return redirect()->route('BonoRegalo.BuscarTarjeta')
                        ->with('warning', $mensaje);
                }
                
                if ($errores > 0) {
                    $mensaje = "❌ Se encontraron <strong>{$errores}</strong> errores en el archivo.<br>";
                    $mensaje .= "✅ Se importaron <strong>{$importados}</strong> tarjetas correctamente.<br><br>";
                    
                    if (count($erroresLista) > 0) {
                        $detalles = "<strong>🔍 Detalles:</strong><ul class='mb-0 mt-1'>";
                        foreach (array_slice($erroresLista, 0, 10) as $error) {
                            $detalles .= "<li class='small text-danger'>" . htmlspecialchars($error) . "</li>";
                        }
                        if (count($erroresLista) > 10) {
                            $detalles .= "<li class='small text-muted'>... y " . (count($erroresLista) - 10) . " errores más.</li>";
                        }
                        $detalles .= "</ul>";
                        $mensaje .= $detalles;
                    }
                    
                    $mensaje .= "<br><small>Log completo en: storage/logs/importacion_" . date('Y-m-d') . ".log</small>";
                    
                    return redirect()->route('BonoRegalo.BuscarTarjeta')
                        ->with('error', $mensaje);
                }
                
                if ($importados > 0) {
                    $mensaje = "🎉 ¡Excelente! Se importaron <strong>{$importados}</strong> tarjetas correctamente.";
                    $mensaje .= "<br><small class='text-muted'>Todas las tarjetas fueron procesadas sin errores.</small>";
                    $mensaje .= "<br><small>Log completo en: storage/logs/importacion_" . date('Y-m-d') . ".log</small>";
                    
                    return redirect()->route('BonoRegalo.BuscarTarjeta')
                        ->with('success', $mensaje);
                }
                
                return redirect()->route('BonoRegalo.BuscarTarjeta')
                    ->with('info', 'El archivo no contenía datos para importar.');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $writeLog("❌ ERROR GENERAL:", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            try {
                Log::error('❌ ERROR GENERAL:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            } catch (\Exception $logError) {
                $writeLog("⚠️ No se pudo escribir en log de Laravel: " . $logError->getMessage());
            }
            
            return redirect()->back()
                ->withErrors(['error' => '❌ Error al procesar: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function plantillaTarjetas()
    {
        $headers = ['numero', 'nit', 'valor', 'estado'];

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers, ';');
            fputcsv($file, ['4198190002092768', '901344877', '50000', 'activa'], ';');
            fputcsv($file, ['4198190049432266', '901344877', '50000', 'inactiva'], ';');
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_tarjetas.csv"',
        ]);
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $tarjeta = TarjetaBonoR::findOrFail($id);
            $numero = $tarjeta->numero;
            $tarjeta->delete();
            DB::commit();

            return redirect()->route('BonoRegalo.BuscarTarjeta')
                ->with('success', '✅ Tarjeta #' . $numero . ' eliminada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar tarjeta: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => '❌ Error al eliminar la tarjeta: ' . $e->getMessage()]);
        }
    }

    public function toggleEstado(Request $request, string $id)
    {
        try {
            $tarjeta = TarjetaBonoR::findOrFail($id);
            $nuevoEstado = $tarjeta->estado === 'activa' ? 'inactiva' : 'activa';
            $tarjeta->update(['estado' => $nuevoEstado]);

            return response()->json([
                'success' => true,
                'message' => "✅ Tarjeta #{$tarjeta->numero} ahora está {$nuevoEstado}",
                'estado' => $nuevoEstado
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Error al cambiar el estado: ' . $e->getMessage()
            ], 500);
        }
    }
}