<?php
// app/Http/Controllers/BC/BcSyncController.php
namespace App\Http\Controllers\BC;

use App\Services\BusinessCentralService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class BcSyncController extends Controller
{
    public function companies(BusinessCentralService $bc)
    {
        return response()->json($bc->listCompanies());
    }

    public function testEjemplo(Request $req, BusinessCentralService $bc)
    {
        $companyId = $req->input('companyId', config('services.bc.company_id'));
        if (!$companyId) {
            return response()->json(['error' => 'Falta companyId'], 400);
        }

        // === Datos de ejemplo (puedes override con query/body si quieres) ===
        $fecha = $req->input('fecha', Carbon::now()->toDateString()); // YYYY-MM-DD
        $factura = $req->input('factura', 'BR001');
        $cedula = $req->input('tercero', '1128474928'); // dimensión/código de tercero
        $tipoDocumento = $req->input('tipoDocumento', 'Invoice');

        // Cuentas
        $glCuentaDetalle = $req->input('ctaDetalle', '41350501'); // G/L Account (ejemplo)
        $bankAccountNo = $req->input('ctaTotal', 'Bank Account'); // Bank Account No. real en BC

        // Dimensiones / códigos
        $libroCodigo = $req->input('libroCodigo', '1_NIIF');
        $codigoConcepto = $req->input('codigoConcepto', '110409');
        $grupoImpuesto = $req->input('grupoImpuesto', 'G COMPR 0%_');
        $areaImpuesto = $req->input('areaImpuesto', 'CLI-NRI'); // ejemplo
        $diario = $req->input('diario', 'BONOREGALO');
        $seccion = $req->input('seccion', 'BONO REG');

        // Tarjeta de ejemplo (DETALLE)
        $numeroTarjeta = $req->input('numeroTarjeta', '4198190052835157');
        $valorDetalle = (float) $req->input('valorDetalle', 50000); // 50,000

        // TOTAL de ejemplo (puede ser igual al detalle o suma de varios)
        $valorTotal = (float) $req->input('valorTotal', 50000);

        // === 1) Línea DETALLE (negativa) ===
        $payloadDetalle = [
            'Diario' => 'BONOREGALO',
            'Seccion' => 'BONO REG',
            'TerceroTipo' => 'G/L Account',
            'NumeroCuenta' => '11051002',
            'Description' => "TarjetaBR N {$numeroTarjeta} (Fac {$factura})",
            'TipoDocumento' => $tipoDocumento,
            'Factura' => $factura,
            'Fecha_Registro' => $fecha,
            'Importe' => -1 * $valorDetalle,

            // Dimensiones / campos adicionales según tu Page 50244
            'Tercero' => (string) $cedula,
            'LibroCodigo' => '1_NIIF',
            'CodigoConcepto' => '110409',
            'GrupoImpuesto' => 'G COMPR 0%_',
            'AreaImpuesto' => $areaImpuesto,
        ];

        $respDetalle = $bc->createCustomJournalLine($companyId, $payloadDetalle);

        // === 2) Línea TOTAL (positiva) ===
        $payloadTotal = [
            'Diario' => 'BONOREGALO',
            'Seccion' => 'BONO REG',
            'TerceroTipo' => 'Bank Account',
            'NumeroCuenta' => 'BANCOLOMBIA 6289 BR', // debe ser el Bank Account No.
            'Description' => "TOTAL Fac {$factura} ({$numeroTarjeta})",
            'TipoDocumento' => $tipoDocumento,
            'Factura' => $factura,
            'Fecha_Registro' => $fecha,
            'Importe' => $valorTotal,

            'Tercero' => (string) $cedula,
            'LibroCodigo' => '1_NIIF',
            'CodigoConcepto' => '110409',
            'GrupoImpuesto' => 'G COMPR 0%_',
            'AreaImpuesto' => $areaImpuesto,
        ];

        $respTotal = $bc->createCustomJournalLine($companyId, $payloadTotal);

        // Devuelve ambas respuestas para que veas el JSON que regresa BC
        return response()->json(
            [
                'detalle_payload' => $payloadDetalle,
                'detalle_resp' => $respDetalle,
                'total_payload' => $payloadTotal,
                'total_resp' => $respTotal,
            ],
            201,
        );
    }

    // (Tu método crearDiario original lo puedes dejar igual, es útil para mandar 1 sola línea ad hoc)
    public function crearDiario(Request $req, BusinessCentralService $bc)
    {
        $companyId = $req->input('companyId', config('services.bc.company_id'));
        if (!$companyId) {
            return response()->json(['error' => 'Falta companyId'], 400);
        }

        $payload = [
            'Diario' => $req->input('Diario', 'BONOREGALO'),
            'Seccion' => $req->input('Seccion', 'BONO REG'),
            'TerceroTipo' => $req->input('TerceroTipo', 'G/L Account'),
            'NumeroCuenta' => $req->input('NumeroCuenta', '41350501'), // por defecto G/L
            'Description' => $req->input('Description', 'Bono Regalo BR-TEST'),
            'TipoDocumento' => $req->input('TipoDocumento', 'Invoice'),
            'Factura' => $req->input('Factura', 'BR-TEST'),
            'Fecha_Registro' => $req->input('Fecha_Registro', now()->toDateString()),
            'Importe' => (float) $req->input('Importe', -50000),

            'Tercero' => $req->input('Tercero', '1128474928'),
            'LibroCodigo' => $req->input('LibroCodigo', '1_NIIF'),
            'CodigoConcepto' => $req->input('CodigoConcepto', '110409'),
            'GrupoImpuesto' => $req->input('GrupoImpuesto', 'G COMPR 0%_'),
            'AreaImpuesto' => $req->input('AreaImpuesto', 'CLI-NRI'),
        ];

        $data = $bc->createCustomJournalLine($companyId, array_filter($payload, fn($v) => $v !== null));
        return response()->json($data, 201);
    }

    public function crearDimension(Request $req, BusinessCentralService $bc)
    {
        $companyId = $req->input('companyId', config('services.bc.company_id'));
        if (!$companyId) {
            return response()->json(['error' => 'Falta companyId'], 400);
        }

        $payload = [
            'DimensionCode' => $req->input('DimensionCode', 'TERCERO'),
            'Code' => $req->input('Code', '1128474928'),
            'Name' => $req->input('Name', 'Tercero 1128474928'),
        ];

        // Evita duplicados
        $data = $bc->upsertDimensionValue($companyId, array_filter($payload, fn($v) => $v !== null));
        return response()->json($data, 201);
    }
}
