<?php

// app/Listeners/MarkOutboxAsSending.php
namespace App\Listeners;

use App\Models\MailOutbox;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\DB;

class MarkOutboxAsSending
{
    public function handle(MessageSending $event): void
    {
        $headers  = $event->message->getHeaders();
        $outboxId = $headers->getHeaderBody('X-Outbox-Id');
        if ($outboxId) {
            MailOutbox::where('id', $outboxId)->update([
                'status'   => 'sending',
                'attempts' => DB::raw('attempts + 1'),
            ]);
        }
    }
}
