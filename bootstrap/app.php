<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureModuloSeleccionado;

return Application::configure(basePath: dirname(__DIR__))
  ->withProviders([
        App\Providers\AppServiceProvider::class,     // si no estaba ya
        App\Providers\EventServiceProvider::class,   // <-- tu provider de eventos
    ])

    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'auth'     => \App\Http\Middleware\Authenticate::class,
            'nivel' => \App\Http\Middleware\CheckModuloNivel::class,
            'admin'    => \App\Http\Middleware\AdminMiddleware::class,
            'enModulo' => EnsureModuloSeleccionado::class,

        ]);

        /**
         * Prioridad de ejecución del middleware
         */
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            session()->invalidate();
            return redirect()->route('login');
        });
    })->create();

