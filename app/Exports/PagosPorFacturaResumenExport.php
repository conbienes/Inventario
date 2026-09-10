<?php

namespace App\Exports;

use App\Models\BonoRegalo\Payment;
use App\Models\BonoRegalo\PaymentMethod;
use App\Models\BonoRegalo\FacturaBonoR;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\DefaultValueBinder;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PagosPorFacturaResumenExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithStyles, WithTitle, WithCustomValueBinder
{
    protected $request;
    protected $tz = 'America/Bogota';

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Resumen Pagos';
    }

    public function collection()
    {
        $tp  = (new Payment)->getTable();
        $tpm = (new PaymentMethod)->getTable();
        $tf  = (new FacturaBonoR)->getTable();

        $q = Payment::query()
            ->from("$tp as p")
            ->join("$tpm as pm", 'pm.id', '=', 'p.method_id')
            ->join("$tf as f", 'f.id', '=', 'p.invoice_id');

        // Filtro de fechas
        if (!$this->request->filled('from') && !$this->request->filled('to')) {
            $hoy = Carbon::now($this->tz)->toDateString();
            $q->whereDate('f.fecha', $hoy);
        } else {
            if ($this->request->filled('from') && $this->request->filled('to')) {
                $fi = Carbon::parse($this->request->from)->startOfDay();
                $ff = Carbon::parse($this->request->to)->endOfDay();
                $q->whereBetween('f.fecha', [$fi, $ff]);
            } elseif ($this->request->filled('from')) {
                $fi = Carbon::parse($this->request->from)->startOfDay();
                $q->where('f.fecha', '>=', $fi);
            } elseif ($this->request->filled('to')) {
                $ff = Carbon::parse($this->request->to)->endOfDay();
                $q->where('f.fecha', '<=', $ff);
            }
        }

        // Pivote por nombre (tolerante a tilde/espacios)
        return $q
            ->groupBy('f.id', 'f.numero_factura', 'f.fecha')
            ->orderBy('f.fecha')
            ->orderBy('f.numero_factura')
            ->get([
                'f.numero_factura as factura',
                'f.fecha as fecha',

                DB::raw("SUM(CASE WHEN TRIM(pm.name) = 'Efectivo' THEN p.amount ELSE 0 END) as efectivo"),
                DB::raw("SUM(CASE WHEN TRIM(pm.name) = 'Llave Bre-B' THEN p.amount ELSE 0 END) as llave_breb"),
                DB::raw("
                    SUM(
                        CASE
                            WHEN TRIM(pm.name) = 'QR Basico'
                              OR TRIM(pm.name) = 'QR Básico'
                            THEN p.amount
                            ELSE 0
                        END
                    ) as qr_basico
                "),
                DB::raw("SUM(CASE WHEN TRIM(pm.name) = 'Transferencia a la cuenta' THEN p.amount ELSE 0 END) as transferencia_cuenta"),
                DB::raw("
                    SUM(
                        CASE
                            WHEN TRIM(pm.name) = 'Wompi Boton Bancolombia'
                              OR TRIM(pm.name) = 'Wompi Botón Bancolombia'
                            THEN p.amount
                            ELSE 0
                        END
                    ) as wompi_boton_bancolombia
                "),
                DB::raw("SUM(CASE WHEN TRIM(pm.name) = 'Wompi PSE' THEN p.amount ELSE 0 END) as wompi_pse"),

                DB::raw('SUM(p.amount) as total_pagado'),
            ]);
    }

    public function headings(): array
    {
        return ['Número Factura', 'Fecha', 'Efectivo', 'Llave Bre-B', 'QR Basico', 'Transferencia a la cuenta', 'Wompi Boton Bancolombia', 'Wompi PSE', 'Total Pagado'];
    }

    public function map($row): array
    {
        $fecha = $row->fecha instanceof Carbon ? $row->fecha->copy()->timezone($this->tz)->startOfDay() : Carbon::parse($row->fecha)->timezone($this->tz)->startOfDay();

        return [
            (string) $row->factura, // A (texto)
            ExcelDate::dateTimeToExcel($fecha), // B (número fecha Excel)
            (float) $row->efectivo, // C
            (float) $row->llave_breb, // D
            (float) $row->qr_basico, // E
            (float) $row->transferencia_cuenta, // F
            (float) $row->wompi_boton_bancolombia, // G
            (float) $row->wompi_pse, // H
            (float) $row->total_pagado, // I
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Fecha como fecha
            'C' => '#,##0.00',
            'D' => '#,##0.00',
            'E' => '#,##0.00',
            'F' => '#,##0.00',
            'G' => '#,##0.00',
            'H' => '#,##0.00',
            'I' => '#,##0.00',
        ];
    }

    // ⚡ Fuerza tipos para evitar "número almacenado como texto"
    public function bindValue(\PhpOffice\PhpSpreadsheet\Cell\Cell $cell, $value)
    {
        $row = $cell->getRow();
        $col = $cell->getColumn();

        // Encabezados (fila 1): siempre TEXTO
        if ($row === 1) {
            $cell->setValueExplicit((string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            return true;
        }

        // Columna A (Número Factura): TEXTO
        if ($col === 'A') {
            $cell->setValueExplicit((string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            return true;
        }

        // Columnas B..I (Fecha y montos): NUMÉRICO
        if (in_array($col, ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'], true)) {
            // B ya viene como serial de fecha desde map(); las demás son montos
            $cell->setValueExplicit((float) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        return [];
    }
}
