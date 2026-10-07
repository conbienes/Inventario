<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    // Sin $listen: Laravel ya descubre automáticamente MarkOutboxAsSending y MarkOutboxAsSent
    // en app/Listeners. Declararlos aquí también los registraba dos veces (attempts se sumaba x2).
    protected $listen = [];

    public function shouldDiscoverEvents(): bool
    {
        return false; // o true si quieres "event discovery"
    }
}
