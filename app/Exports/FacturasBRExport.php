<?php

namespace App\Exports;

use App\Models\BonoRegalo\CargaBC;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\DefaultValueBinder;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class FacturasBRExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithCustomValueBinder
{
    protected $request;
    protected $tz = 'America/Bogota';

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

     public function title(): string
    {
        return 'Ventas Bono Regalo';
    }

    public function collection()
    {
        $table = (new CargaBC)->getTable();

        $q = CargaBC::query()
            ->from("$table as c")
            ->leftJoin('empleados as e', 'e.id', '=', 'c.idEmpleado');

        // SI no hay filtros: hoy
        if (!$this->request->filled('from') && !$this->request->filled('to')) {
            $q->whereDate('c.fecha', Carbon::now($this->tz)->toDateString());
        }

        // from / to (IMPORTANTE: usar from/to, no fecha_inicio/fin)
        if ($this->request->filled('from') && $this->request->filled('to')) {
            $fi = Carbon::parse($this->request->from)->startOfDay();
            $ff = Carbon::parse($this->request->to)->endOfDay();
            $q->whereBetween('c.fecha', [$fi, $ff]);
        } elseif ($this->request->filled('from')) {
            $fi = Carbon::parse($this->request->from)->startOfDay();
            $q->where('c.fecha', '>=', $fi);
        } elseif ($this->request->filled('to')) {
            $ff = Carbon::parse($this->request->to)->endOfDay();
            $q->where('c.fecha', '<=', $ff);
        }

        return $q->get([
            'c.factura',
            'c.tarjeta',
            'c.fecha',
            DB::raw("CONCAT(COALESCE(e.nombre,''),' ',COALESCE(e.apellidos,'')) as vendedor"),
            'c.cedula',
            'c.valor_tarjeta',
        ]);
    }

    public function headings(): array
    {
        return ['Número Factura', 'Tarjeta', 'Fecha', 'Vendedor', 'Cédula Comprador', 'Valor Tarjeta'];
    }

    public function map($row): array
    {
        $fecha = $row->fecha instanceof Carbon
            ? $row->fecha->copy()->timezone($this->tz)->startOfDay()
            : Carbon::parse($row->fecha)->timezone($this->tz)->startOfDay();

        return [
            (string) $row->factura,
            (string) $row->tarjeta,
            ExcelDate::dateTimeToExcel($fecha),
            (string) ($row->vendedor ?? ''),
            (string) $row->cedula,
            (float)  $row->valor_tarjeta,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => 'yyyy-mm-dd',
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => '#,##0.00',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        return [];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), ['B','E'])) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }
}
