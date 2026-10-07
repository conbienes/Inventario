<?php

namespace App\Jobs\BonoRegalo;

use App\Models\BonoRegalo\ClienteBonoR;
use App\Services\BonoRegalo\ClienteBCSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Sincroniza un cliente con BC (cliente + dimensión). El servicio es idempotente, así que admite reintentos. */
class SincronizarClienteBCJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 120;
    public int $uniqueFor = 900;

    public function __construct(public int $clienteId)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->clienteId;
    }

    public function handle(ClienteBCSyncService $service): array
    {
        $cliente = ClienteBonoR::find($this->clienteId);
        if (!$cliente) {
            return [];
        }

        $resultado = $service->sincronizar($cliente);

        // El servicio no lanza excepciones: si falló, se reintenta más tarde (el error queda en bc_error)
        if (!$resultado['ok'] && $this->attempts() < $this->tries) {
            $this->release($this->backoff);
        }

        return $resultado;
    }
}
