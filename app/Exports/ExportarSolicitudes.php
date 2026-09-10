<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class ExportarSolicitudes implements FromView
{
    use Exportable;

    public function view(): View
    {
        $solicitudes = DB::table('pedidoinventario as p')
            ->leftJoin('solicitudpedidos as s', 's.Id', '=', 'p.IdSolicitud')
            ->leftJoin('inventario as i', 'i.Id', '=', 'p.IdInventario')
            ->leftJoin('producto as pr', 'pr.Id', '=', 'i.IdProducto')
            ->leftJoin('empleados as e', 'e.id', '=', 's.IdSolicita')
            ->select(
                'p.IdSolicitud',
                DB::raw('CASE
                            WHEN e.id_div = 1 THEN "Conbienes SA"
                            WHEN e.id_div = 2 THEN "Administracion Mayorca"
                            ELSE "Mayor Diversion"
                         END as Division'),
                'e.nombre',
                'e.apellidos',
                'pr.Nombre',
                'p.CantidadS',
                'p.CantidadAp',
                'p.created_at',
                DB::raw('IF(s.estado = 1, "Activo", "Terminado") as Estado')
            )
            ->get();

        return view('Informes.Solicitudes', ['Solicitud' => $solicitudes]);
    }
}
