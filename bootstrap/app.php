<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureStudent;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Vercel/hosting proxy trust karein taake X-Forwarded-Proto: https se
        // sahi scheme detect ho (warna CSS/JS http:// par load honge).
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin'   => EnsureAdmin::class,
            'student' => EnsureStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
