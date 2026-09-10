<?php

namespace App\Exports;

use App\Models\BonoRegalo\TarjetaBonoR; // ← CAMBIA ESTA LÍNEA
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class TarjetasBRExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = TarjetaBonoR::query(); // ← CAMBIA AQUÍ TAMBIÉN

        // Filtros según la búsqueda
        if ($this->request->filled('numero')) {
            $query->where('numero', 'like', "%{$this->request->numero}%");
        }

        if ($this->request->filled('cedula')) {
            $query->where('nit', 'like', "%{$this->request->cedula}%");
        }

        if ($this->request->filled('estado')) {
            $query->where('estado', $this->request->estado);
        }

        if ($this->request->filled('fecha_inicio') && $this->request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                $this->request->fecha_inicio . ' 00:00:00', 
                $this->request->fecha_fin . ' 23:59:59'
            ]);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'numero',
            'nit',
            'valor',
            'estado',
            'created_at'
        ];
    }

    public function map($tarjeta): array
    {
        return [
            $tarjeta->numero,
            $tarjeta->nit,
            $tarjeta->valor,
            $tarjeta->estado,
            $tarjeta->created_at->format('Y-m-d H:i:s')
        ];
    }
}