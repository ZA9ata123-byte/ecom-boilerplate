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
    ->withMiddleware(function (Middleware $middleware) {
        // خلي CORS أولاً
        $middleware->prepend(\App\Http\Middleware\CorsMiddleware::class);

        // Sanctum (SPA/stateful APIs)
        $middleware->statefulApi();

        // CSRF: استثني الدومينات ديال الفرونت/الAPI (إلا كنت باغي)
        $middleware->validateCsrfTokens(except: [
            'http://localhost:3000/*',
            'http://localhost:8000/*',
        ]);

        // الألياس ديال الميدلوير ديال الأدمين
        $middleware->alias([
            'is.admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
