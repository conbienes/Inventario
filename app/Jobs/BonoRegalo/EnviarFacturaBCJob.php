<?php

namespace App\Jobs\BonoRegalo;

use App\Models\BonoRegalo\FacturaBonoR;
use App\Services\BonoRegalo\FacturaBCService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Envía una factura a BC en segundo plano. Las garantías de no duplicar están en FacturaBCService. */
class EnviarFacturaBCJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // un reintento ciego podría duplicar; los reintentos se hacen desde Contabilidad (estado ERROR)
    public int $timeout = 300;
    public int $uniqueFor = 900;

    public function __construct(public int $facturaId)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->facturaId;
    }

    public function handle(FacturaBCService $service): array
    {
        $factura = FacturaBonoR::with(FacturaBCService::RELACIONES)->find($this->facturaId);

        return $factura ? $service->enviar($factura) : [];
    }
}
