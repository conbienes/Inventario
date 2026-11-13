<?php

namespace App\Services\Inventario;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Consulta de existencias de una división con filtros comunes.
 * La usan Inventario, Alertas de stock y Arqueo para que los filtros sean iguales en las tres pantallas.
 *
 * Parámetros GET aceptados:
 *   texto      nombre o código del producto
 *   categoria  Id de categoría
 *   almacen    Id de almacén
 *   estado     sin (sin existencia) | bajo (bajo el mínimo) | ok (sobre el mínimo) | exceso (sobre el máximo)
 *   orden      producto | cantidad_asc | cantidad_desc | categoria | faltante
 */
class FiltrosInventario
{
    public const ESTADOS = [
        'sin' => 'Sin existencia',
        'bajo' => 'Bajo el mínimo',
        'ok' => 'Stock suficiente',
        'exceso' => 'Sobre el máximo',
    ];

    public const ORDENES = [
        'producto' => 'Producto (A-Z)',
        'categoria' => 'Categoría',
        'cantidad_asc' => 'Menor cantidad primero',
        'cantidad_desc' => 'Mayor cantidad primero',
        'faltante' => 'Lo que más falta primero',
    ];

    public function __construct(private int $division)
    {
    }

    /** Consulta base con los mismos campos que usaban las pantallas, más los necesarios para editar. */
    public function consulta(): Builder
    {
        return DB::table('Inventario')
            ->join('Producto as p', 'p.Id', '=', 'Inventario.IdProducto')
            ->join('Divisiones as d', 'd.Id', '=', 'Inventario.IdDivisiones')
            ->join('Almacen as a', 'a.Id', '=', 'Inventario.IdAlmacen')
            ->join('categoria_inventario as c', 'c.Id', '=', 'Inventario.IdCategoria')
            ->select(
                'Inventario.Id', 'Inventario.Cantidades', 'Inventario.CantidadMin', 'Inventario.CantidadMax',
                'Inventario.Estanteria', 'Inventario.Entrepano', 'Inventario.Gaveta',
                'p.Nombre as Producto', 'p.Codigo', 'd.Nombre as Division', 'd.Id as IdEmpresa',
                'a.Nombre as Almacen', 'c.Nombre as Categoria'
            )
            ->where('d.Id', '=', $this->division);
    }

    /** Aplica los filtros de la petición. $estadoFijo fuerza un estado (p. ej. Alertas = solo bajo el mínimo). */
    public function aplicar(Builder $q, Request $request, ?array $estadosPermitidos = null): Builder
    {
        $texto = strtoupper(trim((string) $request->get('texto', '')));
        if ($texto !== '') {
            $q->where(function ($w) use ($texto) {
                $w->where('p.Nombre', 'like', '%' . $texto . '%')
                  ->orWhere('p.Codigo', 'like', '%' . $texto . '%');
            });
        }

        if ($categoria = (int) $request->get('categoria')) {
            $q->where('Inventario.IdCategoria', $categoria);
        }

        if ($almacen = (int) $request->get('almacen')) {
            $q->where('Inventario.IdAlmacen', $almacen);
        }

        $estado = (string) $request->get('estado', '');
        if ($estadosPermitidos !== null && ! in_array($estado, $estadosPermitidos, true)) {
            $estado = '';
        }
        match ($estado) {
            'sin' => $q->where('Inventario.Cantidades', '<=', 0),
            'bajo' => $q->where('Inventario.Cantidades', '>', 0)->whereColumn('Inventario.Cantidades', '<', 'Inventario.CantidadMin'),
            'ok' => $q->whereColumn('Inventario.Cantidades', '>=', 'Inventario.CantidadMin'),
            'exceso' => $q->where('Inventario.CantidadMax', '>', 0)->whereColumn('Inventario.Cantidades', '>', 'Inventario.CantidadMax'),
            default => null,
        };

        return $q;
    }

    public function ordenar(Builder $q, Request $request, string $porDefecto = 'producto'): Builder
    {
        $orden = (string) $request->get('orden', $porDefecto);

        return match ($orden) {
            'categoria' => $q->orderBy('c.Nombre')->orderBy('p.Nombre'),
            'cantidad_asc' => $q->orderBy('Inventario.Cantidades')->orderBy('p.Nombre'),
            'cantidad_desc' => $q->orderByDesc('Inventario.Cantidades')->orderBy('p.Nombre'),
            'faltante' => $q->orderByRaw('(Inventario.CantidadMin - Inventario.Cantidades) DESC')->orderBy('p.Nombre'),
            default => $q->orderBy('p.Nombre'),
        };
    }

    /** Categorías y almacenes que realmente tiene la división (para las listas de los filtros). */
    public function opciones(): array
    {
        $base = DB::table('Inventario')->where('Inventario.IdDivisiones', $this->division);

        return [
            'categorias' => (clone $base)->join('categoria_inventario as c', 'c.Id', '=', 'Inventario.IdCategoria')
                ->select('c.Id', 'c.Nombre')->distinct()->orderBy('c.Nombre')->get(),
            'almacenes' => (clone $base)->join('Almacen as a', 'a.Id', '=', 'Inventario.IdAlmacen')
                ->select('a.Id', 'a.Nombre')->distinct()->orderBy('a.Nombre')->get(),
        ];
    }
}
