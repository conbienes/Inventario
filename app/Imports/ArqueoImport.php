<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;
use App\Models\Inventario\Movimientos;

/**
 * Carga el conteo físico (arqueo) de UNA empresa.
 *
 * Columnas del Excel que genera la descarga (índice base 0):
 *   0 Id · 1 Almacén · 2 Categoría · 3 Código · 4 Producto · 5 Estantería
 *   6 CANTIDADES (stock en sistema, solo referencia)
 *   7 CANT_ACTUAL (conteo físico: lo que se carga)
 *   8 COMENTARIO (se guarda en el movimiento)
 *   9 CANTIDADES MIN · 10 CANTIDADES MAX
 *  11 IdEmpresa · 12 EMPRESA (marca de la empresa de la descarga)
 *
 * Reglas:
 *  - Todo el archivo se valida antes de guardar. Si hay un solo error, no se cambia nada.
 *  - La empresa del archivo debe ser la empresa activa en el sistema.
 *  - Cada Id debe existir y pertenecer a esa empresa.
 *  - Solo se actualiza lo que cambia: CANT_ACTUAL vacía se omite; CANT_ACTUAL igual a
 *    CANTIDADES no genera cambio (incluye 0 con 0).
 */
class ArqueoImport implements ToCollection
{
    private int $idEmpresa;

    private array $errores = [];
    private int $conteoActualizado = 0;   // productos con nueva cantidad
    private int $minMaxActualizado = 0;   // productos con nuevo mínimo y/o máximo
    private int $sinCambio = 0;           // con conteo, pero igual al sistema
    private int $omitidas = 0;            // sin CANT_ACTUAL

    public function __construct(int $idEmpresa)
    {
        $this->idEmpresa = $idEmpresa;
    }

    public function collection(Collection $rows)
    {
        $filas = $rows->values();

        $this->validarEmpresa($filas);
        if ($this->errores) {
            return;
        }

        // 1) Leer y validar formato de cada fila (sin tocar la base)
        $datos = [];
        $vistos = [];
        foreach ($filas as $indice => $fila) {
            if ($indice === 0) {
                continue; // encabezado
            }
            $numFila = $indice + 1;

            $id = $this->valor($fila, 0);
            if ($id === null) {
                continue; // fila vacía
            }
            if (!is_numeric($id) || (float) $id <= 0 || (float) $id != (int) $id) {
                $this->errores[] = "Fila {$numFila}: el Id '{$id}' no es válido.";
                continue;
            }
            $id = (int) $id;

            if (isset($vistos[$id])) {
                $this->errores[] = "Fila {$numFila}: el Id {$id} está repetido (también en la fila {$vistos[$id]}).";
                continue;
            }
            $vistos[$id] = $numFila;

            $conteo = $this->valor($fila, 7);
            $min = $this->valor($fila, 9);
            $max = $this->valor($fila, 10);

            if ($conteo !== null && (!is_numeric($conteo) || (float) $conteo < 0)) {
                $this->errores[] = "Fila {$numFila}: CANT_ACTUAL '{$conteo}' debe ser un número mayor o igual a 0.";
                continue;
            }
            if ($min !== null && (!is_numeric($min) || (float) $min < 0)) {
                $this->errores[] = "Fila {$numFila}: CANTIDADES MIN '{$min}' debe ser un número mayor o igual a 0.";
                continue;
            }
            if ($max !== null && (!is_numeric($max) || (float) $max < 0)) {
                $this->errores[] = "Fila {$numFila}: CANTIDADES MAX '{$max}' debe ser un número mayor o igual a 0.";
                continue;
            }
            if ($min !== null && $max !== null && (float) $max < (float) $min) {
                $this->errores[] = "Fila {$numFila}: CANTIDADES MAX ({$max}) es menor que MIN ({$min}).";
                continue;
            }

            $datos[] = [
                'fila' => $numFila,
                'id' => $id,
                'conteo' => $conteo !== null ? (float) $conteo : null,
                'min' => $min !== null ? (float) $min : null,
                'max' => $max !== null ? (float) $max : null,
                'comentario' => (string) ($this->valor($fila, 8) ?? ''),
            ];
        }

        if ($this->errores) {
            return;
        }

        // 2) Cada Id debe existir y pertenecer a la empresa activa
        $enEmpresa = DB::table('Inventario')
            ->whereIn('Id', array_keys($vistos))
            ->where('IdDivisiones', $this->idEmpresa)
            ->get()
            ->keyBy('Id');

        foreach ($datos as $d) {
            if (!$enEmpresa->has($d['id'])) {
                $this->errores[] = "Fila {$d['fila']}: el Id {$d['id']} no existe en la empresa activa (puede ser de otra empresa).";
            }
        }

        if ($this->errores) {
            return;
        }

        // 3) Aplicar cambios: todo o nada
        DB::transaction(function () use ($datos, $enEmpresa) {
            foreach ($datos as $d) {
                $actual = $enEmpresa[$d['id']];
                $update = [];

                $cambioStock = $d['conteo'] !== null && $this->distinto($d['conteo'], $actual->Cantidades);
                $cambioMin = $d['min'] !== null && $this->distinto($d['min'], $actual->CantidadMin);
                $cambioMax = $d['max'] !== null && $this->distinto($d['max'], $actual->CantidadMax);

                if ($d['conteo'] === null) {
                    $this->omitidas++;
                }

                if ($cambioStock) {
                    $update['Cantidades'] = $d['conteo'];

                    $mov = new Movimientos();
                    $mov->Cantidad = $d['conteo'];
                    $mov->IdTipoMovimiento = 3;
                    $mov->Descripcion = strtoupper($d['comentario']);
                    $mov->IdEmpleado = auth()->user()->id;
                    $mov->IdArea = auth()->user()->id_area;
                    $mov->IdSolicitante = auth()->user()->id;
                    $mov->IdInventario = $d['id'];
                    $mov->save();

                    $this->conteoActualizado++;
                }

                if ($cambioMin) {
                    $update['CantidadMin'] = $d['min'];
                }
                if ($cambioMax) {
                    $update['CantidadMax'] = $d['max'];
                }
                if ($cambioMin || $cambioMax) {
                    $this->minMaxActualizado++;
                }

                if ($update) {
                    DB::table('Inventario')->where('Id', $d['id'])->update($update);
                } elseif ($d['conteo'] !== null) {
                    $this->sinCambio++;
                }
            }
        });
    }

    /**
     * La descarga marca cada fila con IdEmpresa. Si alguna fila trae otra empresa,
     * se rechaza el archivo completo.
     */
    private function validarEmpresa(Collection $filas): void
    {
        $marcas = [];
        foreach ($filas as $indice => $fila) {
            if ($indice === 0) {
                continue;
            }
            $marca = $this->valor($fila, 11);
            if ($marca !== null) {
                $marcas[(string) $marca] = true;
            }
        }

        if (!$marcas) {
            return; // archivo sin marca: la validación de Ids en la base la cubre
        }

        $empresaActual = DB::table('divisiones')->where('Id', $this->idEmpresa)->value('nombre');

        foreach (array_keys($marcas) as $marca) {
            if (!is_numeric($marca) || (int) $marca !== $this->idEmpresa) {
                $nombre = is_numeric($marca)
                    ? DB::table('divisiones')->where('Id', (int) $marca)->value('nombre')
                    : null;
                $nombre = $nombre ?? "Id {$marca}";

                $this->errores[] = "Este archivo es de {$nombre}, pero estás trabajando en {$empresaActual}. "
                    . 'Cambia de empresa o descarga el arqueo de la empresa correcta.';
                return;
            }
        }
    }

    private function valor($fila, int $indice)
    {
        $valor = $fila[$indice] ?? null;
        if (is_string($valor)) {
            $valor = trim($valor);
            return $valor === '' ? null : $valor;
        }
        return $valor;
    }

    private function distinto($nuevo, $actual): bool
    {
        if ($actual === null) {
            return true;
        }
        return (float) $nuevo !== (float) $actual;
    }

    public function hasErrores(): bool
    {
        return !empty($this->errores);
    }

    public function errores(): array
    {
        return $this->errores;
    }

    public function resumen(): string
    {
        return "Productos con nueva cantidad: {$this->conteoActualizado}. "
            . "Con mínimo/máximo actualizado: {$this->minMaxActualizado}. "
            . "Sin cambio: {$this->sinCambio}. "
            . "Omitidos (CANT_ACTUAL vacía): {$this->omitidas}.";
    }
}
