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
        // الحل النهائي والقاطع: كنسجلو الميدلوير ديالنا هو الأول
        $middleware->prepend(\App\Http\Middleware\CorsMiddleware::class);

        // الإعدادات الأخرى لي كانت ضرورية لـ Sanctum
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: [
            'http://localhost:3000/*',
            'http://localhost:8000/*',
        ]);

        // هنا كيبقى الميدلوير لي كان عندك ديجا
        $middleware->alias([
            'is.admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();