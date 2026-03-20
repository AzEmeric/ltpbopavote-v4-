<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Faire confiance à tous les proxies (Railway, Cloudflare, etc.)
        $middleware->trustProxies(at: '*');

        // Exclure le webhook Moneroo du CSRF (les routes API n'ont pas CSRF par défaut,
        // mais on l'exclut explicitement au cas où)
        $middleware->validateCsrfTokens(except: [
            'api/payment/moneroo/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
