<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Illuminate\Mail\Events\MessageSending::class => [
            \App\Listeners\MarkOutboxAsSending::class,
        ],
        \Illuminate\Mail\Events\MessageSent::class => [
            \App\Listeners\MarkOutboxAsSent::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false; // o true si quieres "event discovery"
    }
}
