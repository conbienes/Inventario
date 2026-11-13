<?php

namespace App\Services\Inventario;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Datos del inicio de Inventario, limitados a la división del usuario.
 * Todas las consultas son de solo lectura.
 */
class ResumenInventario
{
    public function __construct(private int $division)
    {
    }

    /** Indicadores principales: cuántas posiciones y unidades hay, y cuántas están en riesgo. */
    public function indicadores(): array
    {
        $base = DB::table('Inventario')->where('IdDivisiones', $this->division);

        $sinExistencia = (clone $base)->where('Cantidades', '<=', 0)->count();
        $bajoMinimo = (clone $base)
            ->where('CantidadMin', '>', 0)
            ->whereColumn('Cantidades', '<=', 'CantidadMin')
            ->count();

        return [
            'posiciones' => (clone $base)->count(),
            'unidades' => (int) (clone $base)->sum('Cantidades'),
            'sin_existencia' => $sinExistencia,
            'bajo_minimo' => $bajoMinimo,
        ];
    }

    /** Movimientos registrados por mes, últimos $meses meses (incluido el actual). */
    public function movimientosPorMes(int $meses = 12): array
    {
        $inicio = now()->startOfMonth()->subMonths($meses - 1);

        $filas = DB::table('movimientos as m')
            ->join('Inventario as i', 'i.Id', '=', 'm.IdInventario')
            ->where('i.IdDivisiones', $this->division)
            ->where('m.created_at', '>=', $inicio)
            ->select('m.created_at')
            ->get();

        // Agrupado en PHP para no depender de funciones de fecha propias de un motor
        $conteo = [];
        foreach ($filas as $f) {
            $clave = Carbon::parse($f->created_at)->format('Y-m');
            $conteo[$clave] = ($conteo[$clave] ?? 0) + 1;
        }

        $resultado = [];
        for ($i = 0; $i < $meses; $i++) {
            $mes = $inicio->copy()->addMonths($i);
            $clave = $mes->format('Y-m');
            $resultado[] = [
                'etiqueta' => $mes->copy()->locale('es')->isoFormat('MMM YYYY'),
                'valor' => $conteo[$clave] ?? 0,
            ];
        }

        return $resultado;
    }

    /** Productos con más diferencia frente a su mínimo: los que hay que reponer primero. */
    public function reposicion(int $limite = 8): array
    {
        $filas = DB::table('Inventario as i')
            ->join('Producto as p', 'p.Id', '=', 'i.IdProducto')
            ->where('i.IdDivisiones', $this->division)
            ->where('i.CantidadMin', '>', 0)
            ->whereColumn('i.Cantidades', '<=', 'i.CantidadMin')
            ->select('p.Nombre', 'p.Codigo', 'i.Cantidades', 'i.CantidadMin')
            ->orderByRaw('(i.CantidadMin - i.Cantidades) DESC')
            ->limit($limite)
            ->get();

        return $filas->map(function ($f) {
            $falta = max(0, (int) $f->CantidadMin - (int) $f->Cantidades);
            return [
                'producto' => $f->Nombre,
                'codigo' => $f->Codigo,
                'existencia' => (int) $f->Cantidades,
                'minimo' => (int) $f->CantidadMin,
                'faltan' => $falta,
                // Porcentaje de existencia frente al mínimo, para la barra (0 a 100)
                'cobertura' => $f->CantidadMin > 0 ? (int) min(100, round(100 * $f->Cantidades / $f->CantidadMin)) : 0,
            ];
        })->all();
    }

    /** Solicitudes recientes de la división. */
    public function solicitudesRecientes(int $limite = 5): array
    {
        return DB::table('solicitudpedidos as s')
            ->join('empleados as e', 'e.id', '=', 's.IdSolicita')
            ->where('e.id_div', $this->division)
            ->orderByDesc('s.Id')
            ->limit($limite)
            ->select('s.Id', 's.Estado', 's.FechaTentativa', 's.created_at', 'e.nombre', 'e.apellidos')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->Id,
                'solicitante' => trim($s->nombre . ' ' . $s->apellidos),
                'estado' => (int) $s->Estado === 2 ? 'Aprobada' : 'Pendiente',
                'fecha' => $s->created_at ? Carbon::parse($s->created_at)->format('d/m/Y') : '—',
                'aprobada' => (int) $s->Estado === 2,
            ])
            ->all();
    }

    /** Solo las solicitudes del propio usuario (para quien no aprueba). */
    public function miResumen(int $userId, int $limite = 6): array
    {
        $base = DB::table('solicitudpedidos')->where('IdSolicita', $userId);

        $recientes = (clone $base)
            ->orderByDesc('Id')
            ->limit($limite)
            ->get(['Id', 'Estado', 'FechaTentativa', 'created_at'])
            ->map(fn ($s) => [
                'id' => $s->Id,
                'estado' => (int) $s->Estado === 2 ? 'Aprobada' : 'Pendiente',
                'aprobada' => (int) $s->Estado === 2,
                'fecha' => $s->created_at ? Carbon::parse($s->created_at)->format('d/m/Y') : '—',
                'entrega' => $s->FechaTentativa ? Carbon::parse($s->FechaTentativa)->format('d/m/Y') : '—',
            ])
            ->all();

        return [
            'pendientes' => (clone $base)->where('Estado', 1)->count(),
            'aprobadas' => (clone $base)->where('Estado', 2)->count(),
            'recientes' => $recientes,
        ];
    }

    /** Entradas de almacén recientes de la división. */
    public function entradasRecientes(int $limite = 5): array
    {
        return DB::table('registro_producto as r')
            ->leftJoin('Proveedor as p', 'p.Id', '=', 'r.IdProveedor')
            ->where('r.IdDivisiones', $this->division)
            ->orderByDesc('r.Id')
            ->limit($limite)
            ->select('r.Id', 'r.FechaIngreso', 'r.Nfactura', 'r.PrecioTotal', 'p.Nombre as proveedor')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->Id,
                'proveedor' => $r->proveedor ?: 'Sin proveedor',
                'factura' => $r->Nfactura ?: '—',
                'fecha' => $r->FechaIngreso ? Carbon::parse($r->FechaIngreso)->format('d/m/Y') : '—',
                'total' => (float) $r->PrecioTotal,
            ])
            ->all();
    }
}
