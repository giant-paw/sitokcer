<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php', // <-- Ini memuat file web.php Anda
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // INI BAGIAN PENTING YANG HILANG/RUSAK
        // Ini mendaftarkan middleware group 'web' yang berisi
        // semua yang dibutuhkan untuk login/session
        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class, // <-- INI KUNCI UTAMANYA
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Ini adalah middleware 'auth' yang digunakan di web.php
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class, // Untuk Gate 'admin'
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();