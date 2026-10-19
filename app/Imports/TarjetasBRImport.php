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
    /** @var array<int, array{fila: ?int, tarjeta: ?string, mensaje: string}> */
    private array $errores = [];
    private array $columnasFaltantes = [];
    private array $encabezados = [];

    public function __construct(private string $delimitadorCsv = ';', private string $codificacion = 'UTF-8')
    {
    }

    public function getCsvSettings(): array
    {
        return ['delimiter' => $this->delimitadorCsv, 'input_encoding' => $this->codificacion];
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

        // Qué columna del archivo es cada campo (acepta "Número de tarjeta", "No. tarjeta", " valor ", etc.)
        $encabezados = array_keys($rows->first()?->toArray() ?? []);
        $cols = self::resolverColumnas($encabezados);
        $faltan = array_keys(array_filter($cols, fn($c) => $c === null));
        if ($rows->isNotEmpty() && $faltan) {
            $this->filasProcesadas = $rows->count();
            $this->columnasFaltantes = $faltan;
            $this->encabezados = array_map(fn($h) => str_replace('_', ' ', (string) $h), $encabezados);
            return;
        }
        $campo = fn($row, string $c) => $row[$cols[$c]] ?? null;

        foreach ($rows as $i => $row) {
            $fila = $i + 2; // fila 1 = encabezados

            $numeroCrudo = $campo($row, 'numero');
            $numero = $this->numeroTarjeta($numeroCrudo);
            $nit = trim((string) $campo($row, 'nit'));
            $valorOriginal = $campo($row, 'valor');
            $estado = strtolower(trim((string) $campo($row, 'estado')));

            // Fila completamente vacía: se ignora
            if ($numero === '' && $nit === '' && trim((string) $valorOriginal) === '' && $estado === '') {
                continue;
            }

            $this->filasProcesadas++;
            $valor = TarjetaBonoR::parsearValor($valorOriginal);

            $valorTxt = trim((string) $valorOriginal);
            $error = match (true) {
                // Excel guarda los números con 15 dígitos significativos: un número de tarjeta en celda numérica ya viene alterado
                is_int($numeroCrudo) || is_float($numeroCrudo) => 'El número está en una celda numérica de Excel y pierde dígitos. Cambia la columna a formato Texto.',
                $numero === '' => 'Falta el número de tarjeta.',
                !ctype_digit($numero) => 'El número de tarjeta solo puede tener dígitos.',
                strlen($numero) !== 16 => 'El número debe tener 16 dígitos (tiene ' . strlen($numero) . ').',
                $nit === '' => 'Falta el NIT.',
                mb_strlen($nit) > 20 => 'El NIT tiene más de 20 caracteres.',
                $valorTxt === '' => 'Falta el valor.',
                $valor === null || $valor <= 0 => "El valor «{$valorTxt}» no es válido: debe ser un número mayor a 0.",
                $estado === '' => 'Falta el estado.',
                !in_array($estado, ['activa', 'inactiva', 'activo', 'inactivo'], true) => "El estado «{$estado}» no es válido: usa activa o inactiva.",
                isset($validas[$numero]) => 'La tarjeta está repetida en el archivo.',
                default => null,
            };

            if ($error) {
                $this->errores[] = ['fila' => $fila, 'tarjeta' => $numero !== '' ? $numero : null, 'mensaje' => $error];
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
                $this->errores[] = ['fila' => $t['fila'], 'tarjeta' => (string) $numero, 'mensaje' => 'La tarjeta ya está registrada en el sistema.'];
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

    /** Codificación del CSV: UTF-8 o, si no es válido, ANSI (Windows-1252, como lo guarda Excel en español) */
    public static function detectarCodificacion(string $ruta): string
    {
        return mb_check_encoding((string) file_get_contents($ruta), 'UTF-8') ? 'UTF-8' : 'Windows-1252';
    }

    /**
     * Relaciona cada campo (numero, nit, valor, estado) con un encabezado del archivo.
     * Compara sin tildes ni símbolos, en tres pasadas (igual, empieza por, contiene) para que
     * "valor_tarjeta" sea valor y "numero_de_tarjeta" sea numero.
     *
     * @return array<string, string|int|null> campo => encabezado (null si no se encontró)
     */
    public static function resolverColumnas(array $encabezados): array
    {
        $norm = fn($t) => preg_replace('/[^a-z]/', '', strtolower(\Illuminate\Support\Str::ascii((string) $t)));
        $claves = [
            'numero' => ['igual' => ['numero', 'num', 'no', 'tarjeta', 'numerotarjeta'], 'empieza' => ['numero', 'nmero', 'num'], 'contiene' => ['tarjeta']],
            'nit' => ['igual' => ['nit', 'cedula', 'documento'], 'empieza' => ['nit', 'cedula', 'documento'], 'contiene' => ['nit']],
            'valor' => ['igual' => ['valor', 'monto', 'precio'], 'empieza' => ['valor', 'monto', 'precio'], 'contiene' => ['valor']],
            'estado' => ['igual' => ['estado', 'status'], 'empieza' => ['estado', 'status'], 'contiene' => ['estado']],
        ];

        $cols = array_fill_keys(array_keys($claves), null);
        $usados = [];
        foreach (['igual', 'empieza', 'contiene'] as $pasada) {
            foreach ($claves as $campo => $reglas) {
                if ($cols[$campo] !== null) {
                    continue;
                }
                foreach ($encabezados as $h) {
                    $n = $norm($h);
                    if ($n === '' || in_array($h, $usados, true)) {
                        continue;
                    }
                    foreach ($reglas[$pasada] as $k) {
                        $coincide = match ($pasada) {
                            'igual' => $n === $k,
                            'empieza' => str_starts_with($n, $k),
                            'contiene' => str_contains($n, $k),
                        };
                        if ($coincide) {
                            $cols[$campo] = $h;
                            $usados[] = $h;
                            continue 3;
                        }
                    }
                }
            }
        }

        return $cols;
    }

    public function getImportados(): int
    {
        return $this->importados;
    }

    /** Errores por fila, ordenados por número de fila (para mostrarlos en pantalla) */
    public function getErrores(): array
    {
        return collect($this->errores)->sortBy('fila')->values()->all();
    }

    /** Errores en texto plano (para el log) */
    public function getErroresLista(): array
    {
        $lista = array_map(fn($e) => "Fila {$e['fila']}: " . ($e['tarjeta'] ? "{$e['tarjeta']} — " : '') . $e['mensaje'], $this->getErrores());
        if ($this->columnasFaltantes) {
            array_unshift($lista, 'Faltan columnas: ' . implode(', ', $this->columnasFaltantes) . ' (encabezados: ' . implode(', ', $this->encabezados) . ')');
        }

        return $lista;
    }

    /** Columnas obligatorias que no se encontraron en el archivo */
    public function getColumnasFaltantes(): array
    {
        return $this->columnasFaltantes;
    }

    /** Encabezados que trae el archivo (para ayudar a corregirlo) */
    public function getEncabezados(): array
    {
        return $this->encabezados;
    }

    public function getFilasProcesadas(): int
    {
        return $this->filasProcesadas;
    }
}
