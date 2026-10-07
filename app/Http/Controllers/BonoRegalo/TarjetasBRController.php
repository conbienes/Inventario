<?php

namespace App\Http\Controllers\BonoRegalo;

use App\Exports\TarjetasBRExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\BonoRegalo\FiltrosRequest;
use App\Http\Requests\BonoRegalo\ImportarTarjetasRequest;
use App\Http\Requests\BonoRegalo\TarjetaBonoRequest;
use App\Imports\TarjetasBRImport;
use App\Models\BonoRegalo\TarjetaBonoR;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TarjetasBRController extends Controller
{
    public function index()
    {
        return view('BonoRegalo.Tarjetas.index');
    }

    public function BuscarTarjeta(FiltrosRequest $request)
    {
        $hayFiltros = collect($request->only(['numero', 'cedula', 'fecha_inicio', 'fecha_fin']))->filter()->isNotEmpty();
        $validSorts = ['id', 'nit', 'numero', 'valor', 'created_at', 'estado'];

        $Tarjetas = TarjetaBonoR::query()
            ->withExists('itemsFactura as vendida') // para no ofrecer "Editar" en tarjetas vendidas
            ->when($request->filled('numero'), fn($q) => $q->where('numero', 'like', "%{$request->numero}%"))
            ->when($request->filled('cedula'), fn($q) => $q->where('nit', 'like', "%{$request->cedula}%"))
            ->when($request->filled('fecha_inicio'), fn($q) => $q->where('created_at', '>=', Carbon::parse($request->fecha_inicio)->startOfDay()))
            ->when($request->filled('fecha_fin'), fn($q) => $q->where('created_at', '<=', Carbon::parse($request->fecha_fin)->endOfDay()))
            ->when(!$hayFiltros, fn($q) => $q->where('created_at', '>=', now()->startOfDay())) // sin filtros: las de hoy
            ->orderBy(in_array($request->get('sort'), $validSorts, true) ? $request->get('sort') : 'created_at', strtolower($request->get('direction', 'desc')))
            ->paginate(15)
            ->appends($request->query());

        // Totales del inventario en una sola consulta
        $s = TarjetaBonoR::selectRaw("
            COUNT(*) AS total,
            COALESCE(SUM(estado = 'activa'), 0) AS activas,
            COALESCE(SUM(estado = 'inactiva'), 0) AS inactivas,
            COALESCE(SUM(valor), 0) AS valor_total
        ")->first();

        $stats = [
            'total' => (int) $s->total,
            'activas' => (int) $s->activas,
            'inactivas' => (int) $s->inactivas,
            'valor_total' => $s->valor_total,
        ];

        return view('BonoRegalo.Tarjetas.BuscarTarjetas', compact('Tarjetas', 'stats'));
    }

    public function store(TarjetaBonoRequest $request)
    {
        try {
            $tarjeta = TarjetaBonoR::create($request->validated());
        } catch (\Throwable $e) {
            Log::error('Error al guardar tarjeta: ' . TarjetaBonoR::enmascarar($e->getMessage()));

            return back()
                ->withErrors(['error' => '❌ No se pudo guardar la tarjeta. Intenta de nuevo o contacta a soporte.'])
                ->withInput();
        }

        return redirect()->route('BonoRegalo.BuscarTarjeta')
            ->with('success', '✅ Tarjeta #' . $tarjeta->numero . ' registrada correctamente');
    }

    public function edit(string $id)
    {
        $tarjeta = TarjetaBonoR::findOrFail($id);

        $permiso = Gate::inspect('update', $tarjeta);
        if ($permiso->denied()) {
            return redirect()->route('BonoRegalo.BuscarTarjeta')->with('error', $permiso->message());
        }

        return view('BonoRegalo.Tarjetas.editarTarjeta', compact('tarjeta'));
    }

    public function update(TarjetaBonoRequest $request, string $id)
    {
        $tarjeta = TarjetaBonoR::findOrFail($id);

        $permiso = Gate::inspect('update', $tarjeta);
        if ($permiso->denied()) {
            return redirect()->route('BonoRegalo.BuscarTarjeta')->with('error', $permiso->message());
        }

        try {
            $tarjeta->update($request->validated());
        } catch (\Throwable $e) {
            Log::error('Error al actualizar tarjeta: ' . TarjetaBonoR::enmascarar($e->getMessage()));

            return back()
                ->withErrors(['error' => '❌ No se pudo actualizar la tarjeta. Intenta de nuevo o contacta a soporte.'])
                ->withInput();
        }

        return redirect()->route('BonoRegalo.BuscarTarjeta')
            ->with('success', '✅ Tarjeta #' . $tarjeta->numero . ' actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $tarjeta = TarjetaBonoR::findOrFail($id);

        $permiso = Gate::inspect('delete', $tarjeta);
        if ($permiso->denied()) {
            return redirect()->route('BonoRegalo.BuscarTarjeta')->with('error', $permiso->message());
        }

        try {
            $tarjeta->delete();
        } catch (\Throwable $e) {
            Log::error('Error al eliminar tarjeta: ' . TarjetaBonoR::enmascarar($e->getMessage()));

            return back()->withErrors(['error' => '❌ No se pudo eliminar la tarjeta. Intenta de nuevo o contacta a soporte.']);
        }

        return redirect()->route('BonoRegalo.BuscarTarjeta')
            ->with('success', '✅ Tarjeta #' . $tarjeta->numero . ' eliminada correctamente');
    }

    public function exportarTarjetas(FiltrosRequest $request)
    {
        try {
            return Excel::download(new TarjetasBRExport($request), 'tarjetas_bono_' . date('Y-m-d') . '.xlsx');
        } catch (\Throwable $e) {
            Log::error('Error al exportar tarjetas: ' . TarjetaBonoR::enmascarar($e->getMessage()));

            return back()->withErrors(['error' => '❌ No se pudo generar la exportación. Intenta de nuevo o contacta a soporte.']);
        }
    }

    public function importarTarjetas(ImportarTarjetasRequest $request)
    {
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
    private function redirectResultadoImportacion(int $filasProcesadas, int $importados, array $erroresLista)
    {
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

        if ($erroresLista) {
            $lineas = [
                '❌ Se encontraron ' . count($erroresLista) . ' errores en el archivo.',
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
        $callback = function () {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM para que Excel lea UTF-8
            fputcsv($file, ['numero', 'nit', 'valor', 'estado'], ';');
            // Fila de ejemplo con datos ficticios: si se importa sin cambiar, se rechaza (número inválido)
            fputcsv($file, ['NUMERO-DE-16-DIGITOS', '900000000', '50000', 'activa'], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_tarjetas.csv"',
        ]);
    }
}
