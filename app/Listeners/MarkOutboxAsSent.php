<?php

// app/Listeners/MarkOutboxAsSent.php
namespace App\Listeners;

use App\Models\MailOutbox;
use Illuminate\Mail\Events\MessageSent;

class MarkOutboxAsSent
{
    public function handle(MessageSent $event): void
    {
        $email    = $event->message;
        $headers  = $email->getHeaders();
        $outboxId = $headers->getHeaderBody('X-Outbox-Id');

        $messageId = null;
        if (property_exists($event, 'sent') && $event->sent && method_exists($event->sent, 'getMessageId')) {
            $messageId = $event->sent->getMessageId();
        }

        if ($outboxId) {
            MailOutbox::where('id', $outboxId)->update([
                'status'     => 'sent',
                'sent_at'    => now(),
                'message_id' => $messageId,
                'last_error' => null,
            ]);
        }
    }
}
