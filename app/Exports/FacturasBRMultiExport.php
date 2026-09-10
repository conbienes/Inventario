<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FacturasBRMultiExport implements WithMultipleSheets
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function sheets(): array
    {
        return [
            new FacturasBRExport($this->request),            // Hoja 1
            new PagosPorFacturaResumenExport($this->request) // Hoja 2
        ];
    }
}
