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
            Log::error('Error al guardar tarjeta: ' . TarjetaBonoR::enmascarar($e->getMessage()));

            return redirect()
                ->back()
                ->withErrors(['error' => '❌ No se pudo guardar la tarjeta. Intenta de nuevo o contacta a soporte.'])
                ->withInput();
        }
    }

    public function edit(string $id)
    {
        $tarjeta = TarjetaBonoR::findOrFail($id);

        if ($tarjeta->estaVendida()) {
            return redirect()->route('BonoRegalo.BuscarTarjeta')
                ->with('error', 'La tarjeta #' . e($tarjeta->numero) . ' ya fue vendida y no se puede editar.');
        }

        return view('BonoRegalo.Tarjetas.editarTarjeta', compact('tarjeta'));
    }

    public function update(Request $request, string $id)
    {
        $tarjeta = TarjetaBonoR::findOrFail($id);

        if ($tarjeta->estaVendida()) {
            return redirect()->route('BonoRegalo.BuscarTarjeta')
                ->with('error', 'La tarjeta #' . e($tarjeta->numero) . ' ya fue vendida y no se puede editar.');
        }

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
            $tarjeta->update($validatedData);
            DB::commit();

            return redirect()->route('BonoRegalo.BuscarTarjeta')
                ->with('success', '✅ Tarjeta #' . $tarjeta->numero . ' actualizada correctamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar tarjeta: ' . TarjetaBonoR::enmascarar($e->getMessage()));

            return redirect()
                ->back()
                ->withErrors(['error' => '❌ No se pudo actualizar la tarjeta. Intenta de nuevo o contacta a soporte.'])
                ->withInput();
        }
    }

    public function exportarTarjetas(Request $request)
    {
        try {
            return Excel::download(new TarjetasBRExport($request), 'tarjetas_bono_' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Error al exportar tarjetas: ' . TarjetaBonoR::enmascarar($e->getMessage()));
            return redirect()->back()
                ->withErrors(['error' => '❌ No se pudo generar la exportación. Intenta de nuevo o contacta a soporte.']);
        }
    }

    public function importarTarjetas(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:2048|mimes:csv,txt,xlsx,xls',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo para importar.',
            'archivo.max' => 'El archivo no debe superar los 2MB.',
            'archivo.mimes' => 'Solo se permiten archivos .csv, .xlsx o .xls.',
        ]);

        $archivo = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());
        $tipos = ['csv' => \Maatwebsite\Excel\Excel::CSV, 'xlsx' => \Maatwebsite\Excel\Excel::XLSX, 'xls' => \Maatwebsite\Excel\Excel::XLS];

        if (!isset($tipos[$extension])) {
            return back()->withErrors(['archivo' => 'Solo se permiten archivos .csv, .xlsx o .xls.']);
        }

        try {
            $delimitador = $extension === 'csv' ? TarjetasBRImport::detectarDelimitador($archivo->getRealPath()) : ';';
            $import = new TarjetasBRImport($delimitador);
            Excel::import($import, $archivo, null, $tipos[$extension]);

            Log::info('Importación de tarjetas Bono Regalo', [
                'user_id' => auth()->id(),
                'archivo' => $archivo->getClientOriginalName(),
                'filas' => $import->getFilasProcesadas(),
                'importadas' => $import->getImportados(),
                'errores' => array_map([TarjetaBonoR::class, 'enmascarar'], $import->getErroresLista()),
            ]);

            return $this->redirectResultadoImportacion($import->getFilasProcesadas(), $import->getImportados(), $import->getErroresLista());
        } catch (\Throwable $e) {
            Log::error('Error al importar tarjetas: ' . TarjetaBonoR::enmascarar($e->getMessage()));

            return back()->withErrors(['error' => '❌ Ocurrió un error al procesar el archivo. Intenta de nuevo o contacta a soporte.']);
        }
    }

    // Mensaje de resultado de importación en texto plano (la vista lo escapa)
    private function redirectResultadoImportacion(int $filasProcesadas, int $importados, array $erroresLista, ?int $totalErrores = null)
    {
        $totalErrores ??= count($erroresLista);
        $ruta = redirect()->route('BonoRegalo.BuscarTarjeta');

        if ($filasProcesadas == 0) {
            return $ruta->with('warning', implode("\n", [
                '⚠️ El archivo fue procesado pero no se encontraron datos.',
                'Posibles causas:',
                '• Los encabezados no coinciden: deben ser numero, nit, valor, estado',
                '• El archivo está vacío o solo tiene encabezados',
                '• El separador no es punto y coma (;) o coma (,)',
            ]));
        }

        if ($totalErrores > 0) {
            $lineas = [
                "❌ Se encontraron {$totalErrores} errores en el archivo.",
                "✅ Se importaron {$importados} tarjetas correctamente.",
                'Detalles:',
            ];
            foreach (array_slice($erroresLista, 0, 10) as $error) {
                $lineas[] = '• ' . $error;
            }
            if (count($erroresLista) > 10) {
                $lineas[] = '… y ' . (count($erroresLista) - 10) . ' errores más.';
            }

            return $ruta->with('error', implode("\n", $lineas));
        }

        if ($importados > 0) {
            return $ruta->with('success', "🎉 Se importaron {$importados} tarjetas correctamente, sin errores.");
        }

        return $ruta->with('info', 'El archivo no contenía datos para importar.');
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

            if ($tarjeta->estaVendida()) {
                DB::rollBack();
                return redirect()->route('BonoRegalo.BuscarTarjeta')
                    ->with('error', 'La tarjeta #' . e($tarjeta->numero) . ' ya fue vendida y no se puede eliminar.');
            }

            $numero = $tarjeta->numero;
            $tarjeta->delete();
            DB::commit();

            return redirect()->route('BonoRegalo.BuscarTarjeta')
                ->with('success', '✅ Tarjeta #' . $numero . ' eliminada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar tarjeta: ' . TarjetaBonoR::enmascarar($e->getMessage()));

            return redirect()->back()
                ->withErrors(['error' => '❌ No se pudo eliminar la tarjeta. Intenta de nuevo o contacta a soporte.']);
        }
    }

    public function toggleEstado(Request $request, string $id)
    {
        try {
            $tarjeta = TarjetaBonoR::findOrFail($id);

            if ($tarjeta->estaVendida()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La tarjeta ya fue vendida y no se puede cambiar de estado.',
                ], 409);
            }

            $nuevoEstado = $tarjeta->estado === 'activa' ? 'inactiva' : 'activa';
            $tarjeta->update(['estado' => $nuevoEstado]);

            return response()->json([
                'success' => true,
                'message' => "✅ Tarjeta #{$tarjeta->numero} ahora está {$nuevoEstado}",
                'estado' => $nuevoEstado
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado: ' . TarjetaBonoR::enmascarar($e->getMessage()));
            return response()->json([
                'success' => false,
                'message' => '❌ No se pudo cambiar el estado de la tarjeta.'
            ], 500);
        }
    }
}
