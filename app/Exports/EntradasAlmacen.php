<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;
use Exception;

class EntradasAlmacen implements FromView
{
    use Exportable;

    public function view(): View
    {
        try {
            $entradas = DB::table('itemfactura as i')
                ->leftJoin('registro_producto as r', 'i.IdRegProduc', '=', 'r.Id')
                ->leftJoin('divisiones as d', 'd.id', '=', 'r.IdDivisiones')
                ->leftJoin('proveedor as p', 'p.Id', '=', 'r.IdProveedor')
                ->leftJoin('inventario as inv', 'inv.Id', '=', 'i.IdInventario')
                ->leftJoin('producto as pro', 'pro.Id', '=', 'inv.IdProducto')
                ->leftJoin('estados as e', 'e.id', '=', 'r.Estado')
                ->select(
                    'r.Id as Id',
                    'd.nombre as Division',
                    'p.Nit as Nit',
                    'p.Nombre as Proveedor',
                    'r.Nfactura as Factura',
                    'r.PrecioTotal as PrecioTotal',
                    'pro.Nombre as ItemProductos',
                    'i.CantidadS as CantidadSolicitada',
                    'i.CantidadA as CantidadRecibidas',
                    'r.Calificacion',
                    'i.Comentario',
                    'r.OrdenPedido',
                    'e.nombre as Estado',
                    'r.FechaIngreso as FechaCreacion'
                )
                ->orderBy('r.Id')
                ->get();

            return view('Informes.EntradasAlmacen', ['EntradasAlmacen' => $entradas]);

        } catch (Exception $e) {
            // Puedes registrar el error para consultarlo luego
            dd('Error al generar el informe de Entradas Almacen: ' . $e->getMessage());

            // También puedes lanzar una excepción personalizada o mostrar un mensaje
            abort(500, 'Error al generar el informe de Entradas Almacen.');
        }
    }
}
