<?php

namespace App\Mail;

use App\Models\MailOutbox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\Mime\Email;
use Throwable;

class FacturaBonoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $cliente;
    public $factura;
    public array $items;
    public int $outboxId;

    // Reintentos / backoff a nivel de Job del mailable
    public int $tries = 3;
    public int $backoff = 60;

    /**
     * @param  mixed  $cliente
     * @param  mixed  $factura
     * @param  array  $items
     * @param  int    $outboxId   ID del registro en mail_outbox
     */
    public function __construct($cliente, $factura, array $items, int $outboxId)
    {
        $this->cliente   = $cliente;
        $this->factura   = $factura;
        $this->items     = $items;
        $this->outboxId  = $outboxId;

        // Asegura que solo se encole si la transacción hace commit
        $this->afterCommit = true;
    }

    /**
     * Idempotencia: si el recibo ya se envió (p. ej. el job quedó duplicado por
     * outbox:send-queued o un reintento), no se vuelve a enviar al cliente.
     */
    public function send($mailer)
    {
        if (MailOutbox::whereKey($this->outboxId)->value('status') === 'sent') {
            return null;
        }

        return parent::send($mailer);
    }

    public function build()
    {
        $empresa = config('bono_regalo.empresa') + ['titulo' => 'RECIBOS DE CAJA'];

        $pdf = Pdf::loadView('BonoRegalo.PDF.recibo', [
            'cliente' => $this->cliente,
            'factura' => $this->factura,
            'items'   => $this->items,
            'empresa' => $empresa,
        ])->setPaper('letter');

        return $this->subject('Gracias Por Su Compra ')
            ->view('Emails.facturaBono')
            ->with([
                'cliente' => $this->cliente,
                'factura' => $this->factura,
                'items'   => $this->items,
            ])
            ->attachData(
                $pdf->output(),
                'Recibo de Caja '.$this->factura->numero_factura.'.pdf',
                ['mime' => 'application/pdf']
            )
            ->withSymfonyMessage(function (Email $email) {
                // Header para que los listeners identifiquen el outbox
                $email->getHeaders()->addTextHeader('X-Outbox-Id', (string) $this->outboxId);
            });
    }

    /**
     * Se ejecuta cuando el Job que envía este mailable falla.
     */
    public function failed(Throwable $e): void
    {
        MailOutbox::where('id', $this->outboxId)->update([
            'status'     => 'failed',
            'failed_at'  => now(),
            'last_error' => mb_substr($e->getMessage(), 0, 65000),
        ]);
    }
}
