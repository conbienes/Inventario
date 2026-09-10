<?php

namespace App\Imports;

use App\Models\BonoRegalo\TarjetaBonoR;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;

class TarjetasBRImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    private $importados = 0;
    private $errores = 0;
    private $erroresLista = [];
    private $filasProcesadas = 0;

    public function model(array $row)
    {
        $this->filasProcesadas++;
        
        // LOG DE CADA FILA PROCESADA
        Log::info('📄 FILA ' . $this->filasProcesadas . ' PROCESADA:', $row);
        
        try {
            // Buscar las columnas por cualquier nombre posible
            $numero = $this->getValue($row, ['numero', 'Numero', 'Número', 'NUMERO']);
            $nit = $this->getValue($row, ['nit', 'Nit', 'NIT', 'cedula', 'Cedula']);
            $valor = $this->getValue($row, ['valor', 'Valor', 'VALOR', 'monto', 'Monto']);
            $estado = $this->getValue($row, ['estado', 'Estado', 'ESTADO', 'status', 'Status']);

            Log::info('🔍 DATOS EXTRAÍDOS:', [
                'numero' => $numero,
                'nit' => $nit,
                'valor' => $valor,
                'estado' => $estado
            ]);

            // Si no hay datos, saltar
            if (empty($numero) && empty($nit) && empty($valor) && empty($estado)) {
                Log::warning('⚠️ Fila vacía, saltando');
                return null;
            }

            // Validaciones detalladas
            if (empty($numero)) {
                $this->errores++;
                $this->erroresLista[] = "❌ Fila {$this->filasProcesadas}: Número de tarjeta vacío";
                Log::warning('❌ Número vacío en fila ' . $this->filasProcesadas);
                return null;
            }

            $numero = trim((string) $numero);
            if (strlen($numero) != 16) {
                $this->errores++;
                $this->erroresLista[] = "❌ Fila {$this->filasProcesadas}: Número '$numero' debe tener 16 dígitos (tiene " . strlen($numero) . ")";
                Log::warning('❌ Número incorrecto en fila ' . $this->filasProcesadas . ': ' . $numero);
                return null;
            }

            if (!is_numeric($numero)) {
                $this->errores++;
                $this->erroresLista[] = "❌ Fila {$this->filasProcesadas}: Número '$numero' debe ser solo números";
                return null;
            }

            if (empty($nit)) {
                $this->errores++;
                $this->erroresLista[] = "❌ Fila {$this->filasProcesadas}: NIT vacío para tarjeta $numero";
                return null;
            }

            $valor = floatval($valor);
            if ($valor <= 0) {
                $this->errores++;
                $this->erroresLista[] = "❌ Fila {$this->filasProcesadas}: Valor '$valor' debe ser mayor a 0 para tarjeta $numero";
                return null;
            }

            if (empty($estado)) {
                $this->errores++;
                $this->erroresLista[] = "❌ Fila {$this->filasProcesadas}: Estado vacío para tarjeta $numero";
                return null;
            }

            $estado = strtolower(trim($estado));
            if (!in_array($estado, ['activa', 'inactiva', 'activo', 'inactivo'])) {
                $this->errores++;
                $this->erroresLista[] = "❌ Fila {$this->filasProcesadas}: Estado '$estado' no válido para tarjeta $numero. Debe ser: activa, activo, inactiva o inactivo";
                return null;
            }

            // Normalizar estado
            $estadoNormalizado = in_array($estado, ['activa', 'activo']) ? 'activa' : 'inactiva';

            // Verificar duplicado
            $existe = TarjetaBonoR::where('numero', $numero)->exists();
            if ($existe) {
                $this->errores++;
                $this->erroresLista[] = "⚠️ Fila {$this->filasProcesadas}: Tarjeta duplicada: $numero (ya existe)";
                return null;
            }

            $this->importados++;
            Log::info('✅ Tarjeta válida para importar:', ['numero' => $numero, 'nit' => $nit, 'valor' => $valor, 'estado' => $estadoNormalizado]);
            
            return new TarjetaBonoR([
                'numero' => $numero,
                'nit' => trim((string) $nit),
                'valor' => $valor,
                'estado' => $estadoNormalizado,
            ]);

        } catch (\Exception $e) {
            $this->errores++;
            $this->erroresLista[] = "💥 Fila {$this->filasProcesadas}: Error: " . $e->getMessage();
            Log::error('💥 Error en fila ' . $this->filasProcesadas . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un valor del array buscando por múltiples nombres de columna
     */
    private function getValue($row, $keys)
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && !empty($row[$key])) {
                return $row[$key];
            }
        }
        return null;
    }

    public function rules(): array
    {
        return [
            'numero' => 'required|digits:16|numeric',
            'nit' => 'required|string|max:20',
            'valor' => 'required|numeric|min:0',
            'estado' => 'required|in:activa,inactiva,activo,inactivo',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'numero.required' => '❌ El número de tarjeta es obligatorio.',
            'numero.digits' => '❌ El número de tarjeta debe tener 16 dígitos.',
            'numero.numeric' => '❌ El número de tarjeta debe ser numérico.',
            'nit.required' => '❌ El NIT es obligatorio.',
            'valor.required' => '❌ El valor es obligatorio.',
            'valor.numeric' => '❌ El valor debe ser numérico.',
            'valor.min' => '❌ El valor no puede ser negativo.',
            'estado.required' => '❌ El estado es obligatorio.',
            'estado.in' => '❌ El estado debe ser "activa", "activo", "inactiva" o "inactivo".',
        ];
    }

    public function getImportados()
    {
        return $this->importados;
    }

    public function getErrores()
    {
        return $this->errores;
    }

    public function getErroresLista()
    {
        return $this->erroresLista;
    }

    public function getFilasProcesadas()
    {
        return $this->filasProcesadas;
    }
}