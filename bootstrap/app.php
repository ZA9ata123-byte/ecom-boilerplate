<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ فعّل دعم الـ SPA/Sanctum (كيزيد ميدلويرات ديالو)
        $middleware->statefulApi();

        // ✅ خلّي CorsMiddleware ديالك في آخر الستّاك باش يعوّض أي هيدر سابق

        // (اختياري للتطوير) استثناءات CSRF
        $middleware->validateCsrfTokens(except: [
            'http://localhost:3000/*',
            'http://localhost:8000/*',
        ]);

        // (اختياري) ألياس لميدلوير الأدمن
        $middleware->alias([
            'is.admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // معالجات استثناءات مخصّصة (إذا احتجت)
    })
    ->create();
