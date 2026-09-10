<?php
// app/Exports/VentasExport.php
namespace App\Exports;

use App\Models\BonoRegalo\Payment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VentasExport implements FromCollection, WithHeadings
{
    public function __construct(public string $from, public string $to) {}

    public function collection()
    {
        $statusOk = ['approved','paid','success','completed'];
        return Payment::with(['method','invoice'])
            ->whereBetween('created_at', [Carbon::parse($this->from)->startOfDay(), Carbon::parse($this->to)->endOfDay()])
            ->whereIn('status',$statusOk)
            ->get()
            ->map(function($p){
                return [
                    'fecha'      => optional($p->created_at)->format('Y-m-d H:i'),
                    'metodo'     => $p->method->name ?? 'N/D',
                    'factura'    => $p->invoice->numero_factura ?? $p->invoice_id,
                    'valor'      => $p->amount,
                    'estado'     => $p->status,
                    'referencia' => $p->reference,
                ];
            });
    }

    public function headings(): array
    {
        return ['Fecha','Método','Factura','Valor','Estado','Referencia'];
    }
}
