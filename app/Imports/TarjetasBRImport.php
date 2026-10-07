<?php

namespace App\Imports;

use App\Models\BonoRegalo\TarjetaBonoR;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

/**
 * Importa tarjetas Bono Regalo desde CSV, XLSX o XLS (columnas: numero, nit, valor, estado).
 *
 * - Valida cada fila y reporta los errores por número de fila.
 * - Revisa duplicados (contra la BD y dentro del mismo archivo) con una sola consulta.
 * - Inserta las filas válidas; las inválidas se reportan y no se guardan.
 */
// StringValueBinder: las celdas del CSV se leen como texto tal cual ("50.000" no se convierte en 50,
// y los números de tarjeta de 16 dígitos no pierden precisión al pasar por float)
class TarjetasBRImport extends StringValueBinder implements ToCollection, WithHeadingRow, WithCustomCsvSettings, WithCustomValueBinder
{
    private int $importados = 0;
    private int $filasProcesadas = 0;
    private array $erroresLista = [];

    public function __construct(private string $delimitadorCsv = ';')
    {
    }

    public function getCsvSettings(): array
    {
        return ['delimiter' => $this->delimitadorCsv, 'input_encoding' => 'UTF-8'];
    }

    /** Detecta el separador mirando la línea de encabezados del CSV */
    public static function detectarDelimitador(string $ruta): string
    {
        $fh = fopen($ruta, 'r');
        $primera = (string) fgets($fh);
        fclose($fh);
        foreach ([';', ',', "\t", '|'] as $sep) {
            if (str_contains($primera, $sep)) {
                return $sep;
            }
        }

        return ';';
    }

    public function collection(Collection $rows): void
    {
        $validas = [];

        foreach ($rows as $i => $row) {
            $fila = $i + 2; // fila 1 = encabezados

            $numeroCrudo = $this->valor($row, ['numero', 'num', 'tarjeta']);
            $numero = $this->numeroTarjeta($numeroCrudo);
            $nit = trim((string) $this->valor($row, ['nit', 'cedula']));
            $valorOriginal = $this->valor($row, ['valor', 'monto']);
            $estado = strtolower(trim((string) $this->valor($row, ['estado', 'status'])));

            // Fila completamente vacía: se ignora
            if ($numero === '' && $nit === '' && trim((string) $valorOriginal) === '' && $estado === '') {
                continue;
            }

            $this->filasProcesadas++;
            $valor = TarjetaBonoR::parsearValor($valorOriginal);

            $error = match (true) {
                // Excel guarda los números con 15 dígitos significativos: un número de tarjeta en celda numérica ya viene alterado
                is_int($numeroCrudo) || is_float($numeroCrudo) => "Número de tarjeta en celda numérica de Excel (pierde dígitos). Formatea la columna numero como Texto y vuelve a escribirlo",
                !preg_match('/^\d{16}$/', $numero) => "Número '{$numero}' inválido: debe tener exactamente 16 dígitos",
                $nit === '' => "NIT vacío para tarjeta {$numero}",
                mb_strlen($nit) > 20 => "NIT demasiado largo para tarjeta {$numero}",
                $valor === null || $valor <= 0 => "Valor '" . trim((string) $valorOriginal) . "' no es válido (debe ser mayor a 0) para tarjeta {$numero}",
                !in_array($estado, ['activa', 'inactiva', 'activo', 'inactivo'], true) => "Estado '{$estado}' no válido para tarjeta {$numero}. Debe ser: activa o inactiva",
                isset($validas[$numero]) => "Tarjeta {$numero} repetida dentro del archivo",
                default => null,
            };

            if ($error) {
                $this->erroresLista[] = "Fila {$fila}: {$error}";
                continue;
            }

            $validas[$numero] = [
                'fila' => $fila,
                'numero' => $numero,
                'nit' => $nit,
                'valor' => $valor,
                'estado' => in_array($estado, ['activa', 'activo'], true) ? 'activa' : 'inactiva',
            ];
        }

        // Duplicados contra la BD: una sola consulta
        $existentes = TarjetaBonoR::whereIn('numero', array_keys($validas))->pluck('numero')->flip();
        foreach ($validas as $numero => $t) {
            if ($existentes->has($numero)) {
                $this->erroresLista[] = "Fila {$t['fila']}: Tarjeta {$numero} ya existe";
                unset($validas[$numero]);
            }
        }

        if (!$validas) {
            return;
        }

        $ahora = now();
        $registros = array_map(fn($t) => [
            'numero' => $t['numero'],
            'nit' => $t['nit'],
            'valor' => $t['valor'],
            'estado' => $t['estado'],
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ], array_values($validas));

        DB::transaction(function () use ($registros) {
            foreach (array_chunk($registros, 500) as $lote) {
                TarjetaBonoR::insert($lote);
            }
        });

        $this->importados = count($registros);
    }

    /** Excel puede entregar el número como float: se convierte sin notación científica */
    private function numeroTarjeta($v): string
    {
        if (is_int($v) || is_float($v)) {
            return sprintf('%.0f', $v);
        }

        return preg_replace('/\s+/', '', trim((string) $v));
    }

    /** Valor de la primera columna que exista entre los nombres aceptados (encabezados ya normalizados) */
    private function valor($row, array $claves)
    {
        foreach ($claves as $k) {
            if (isset($row[$k]) && $row[$k] !== '') {
                return $row[$k];
            }
        }

        return null;
    }

    public function getImportados(): int
    {
        return $this->importados;
    }

    public function getErrores(): int
    {
        return count($this->erroresLista);
    }

    public function getErroresLista(): array
    {
        return $this->erroresLista;
    }

    public function getFilasProcesadas(): int
    {
        return $this->filasProcesadas;
    }
}
