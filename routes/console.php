<?php
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

// 👇 Ajusta si tus clases están en otros namespaces
use App\Models\MailOutbox;
use App\Models\BonoRegalo\ClienteBonoR;
use App\Models\BonoRegalo\FacturaBonoR;
use App\Mail\FacturaBonoMail;

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

// Procesa la cola (correos de recibos, envíos a BC) cada minuto.
// Requiere una tarea de Windows que ejecute "php artisan schedule:run" cada minuto.
Schedule::command('queue:work --stop-when-empty --tries=3 --backoff=60 --max-time=55')
    ->everyMinute()
    ->withoutOverlapping(10);

/**
 * IDs de mail_outbox que ya tienen un job pendiente en la cola,
 * para no encolarlos de nuevo (evita trabajos duplicados).
 */
function outboxIdsEnCola(): array
{
    return DB::table('jobs')
        ->where('payload', 'like', '%FacturaBonoMail%')
        ->pluck('payload')
        ->map(function ($p) {
            $cmd = json_decode($p, true)['data']['command'] ?? '';
            return preg_match('/outboxId";i:(\d+)/', $cmd, $m) ? (int) $m[1] : null;
        })
        ->filter()
        ->flip()
        ->all();
}

// Enviar pendientes a las 5:00 PM y 11:00 PM (hora de Colombia)
Schedule::command('outbox:send-queued')
    ->cron('0 17,23 * * *')
    ->timezone('America/Bogota');

// (Opcional) Reintentar fallidos 5 minutos después
Schedule::command('outbox:resend-failed')
    ->cron('5 17,23 * * *')
    ->timezone('America/Bogota');


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Encola todos los correos en estado "queued" de mail_outbox.
 * Uso: php artisan outbox:send-queued  (opciones: --chunk=100)
 */
Artisan::command('outbox:send-queued {--chunk=100}', function () {
    $chunk = (int) $this->option('chunk');

    $this->info('Encolando correos en estado "queued"...');

    $enCola = outboxIdsEnCola();

    MailOutbox::where('status', 'queued')
        ->orderBy('id')
        ->chunkById($chunk, function ($rows) use ($enCola) {
            foreach ($rows as $out) {
                // Ya tiene un job pendiente: no duplicarlo
                if (isset($enCola[$out->id])) {
                    continue;
                }

                // Reconstruir datos
                $cliente = $out->cliente_id ? ClienteBonoR::find($out->cliente_id) : null;
                $factura = $out->factura_id ? FacturaBonoR::find($out->factura_id) : null;

                // payload puede estar como JSON (string) o array
                $payload = is_array($out->payload)
                    ? $out->payload
                    : (json_decode($out->payload ?? '[]', true) ?: []);

                $items = (array) ($payload['items'] ?? []);

                // Encolar mailable (ShouldQueue)
                Mail::to($out->to)->queue(
                    (new FacturaBonoMail($cliente, $factura, $items, $out->id))
                        ->afterCommit()
                );

                $this->info("Encolado outbox #{$out->id} → {$out->to}");
            }
        });

    $this->info('Listo. Ahora ejecuta el worker: php artisan queue:work --tries=3 --backoff=60');
})->purpose('Queue all queued mails from mail_outbox');

/**
 * Re-encola correos en estado "failed".
 * Uso: php artisan outbox:resend-failed  (opciones: --chunk=100)
 */
Artisan::command('outbox:resend-failed {--chunk=100}', function () {
    $chunk = (int) $this->option('chunk');

    $this->info('Re-encolando correos en estado "failed"...');

    MailOutbox::where('status', 'failed')
        ->orderBy('id')
        ->chunkById($chunk, function ($rows) {
            foreach ($rows as $out) {
                $cliente = $out->cliente_id ? ClienteBonoR::find($out->cliente_id) : null;
                $factura = $out->factura_id ? FacturaBonoR::find($out->factura_id) : null;

                $payload = is_array($out->payload)
                    ? $out->payload
                    : (json_decode($out->payload ?? '[]', true) ?: []);

                $items = (array) ($payload['items'] ?? []);

                Mail::to($out->to)->queue(
                    (new FacturaBonoMail($cliente, $factura, $items, $out->id))
                        ->afterCommit()
                );

                // Volver a "queued" para que el flujo normal los procese
                $out->update([
                    'status'     => 'queued',
                    'queued_at'  => now(),
                    'last_error' => null,
                ]);

                $this->info("Re-encolado outbox #{$out->id}");
            }
        });

    $this->info('Listo. Ejecuta el worker: php artisan queue:work --tries=3 --backoff=60');
})->purpose('Re-queue failed mails from mail_outbox');

/**
 * (Opcional) Encola un solo correo por ID.
 * Uso: php artisan outbox:send 123
 */
Artisan::command('outbox:send {id}', function (int $id) {
    $out = MailOutbox::findOrFail($id);

    $cliente = $out->cliente_id ? ClienteBonoR::find($out->cliente_id) : null;
    $factura = $out->factura_id ? FacturaBonoR::find($out->factura_id) : null;

    $payload = is_array($out->payload)
        ? $out->payload
        : (json_decode($out->payload ?? '[]', true) ?: []);

    $items = (array) ($payload['items'] ?? []);

    Mail::to($out->to)->queue(
        (new FacturaBonoMail($cliente, $factura, $items, $out->id))
            ->afterCommit()
    );

    // Si estaba fallido, lo regresamos a queued
    if ($out->status === 'failed') {
        $out->update([
            'status'     => 'queued',
            'queued_at'  => now(),
            'last_error' => null,
        ]);
    }

    $this->info("Encolado outbox #{$out->id} → {$out->to}. Ejecuta: php artisan queue:work");
})->purpose('Queue a single mail_outbox item by id');
