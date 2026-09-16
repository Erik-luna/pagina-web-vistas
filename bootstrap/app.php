<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // <-- Asegúrate de incluir esta línea arriba

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Confiar en todos los proxies de la plataforma en la nube (Northflank)
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'auth' => \App\Http\Middleware\Autenticar::class,
            'rol' => \App\Http\Middleware\VerificarRol::class,
            'auth-api' => \App\Http\Middleware\AutenticarApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();