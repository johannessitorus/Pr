<?php

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
        // Daftarkan alias middleware di sini
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'dosen' => \App\Http\Middleware\DosenMiddleware::class,
            'mahasiswa' => \App\Http\Middleware\MahasiswaMiddleware::class,
            // alias lain jika perlu
        ]);

        // Anda juga bisa menambahkan middleware ke grup tertentu di sini
        // $middleware->web(append: [
        //     \App\Http\Middleware\ExampleMiddleware::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
