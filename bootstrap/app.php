<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Middlewares
use App\Http\Middleware\ActivityLogger;
use App\Http\Middleware\Authenticate;   // ← تأكد أنه موجود

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // 🔥 إضافة Activity Logger لجروب الـ API
        $middleware->api(append: [
            ActivityLogger::class,
        ]);

        // 🔥 تسجيل alias للـ middleware
        $middleware->alias([
            'auth' => Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
