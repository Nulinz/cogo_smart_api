<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'tenant.db' => \App\Http\Middleware\Dynamic_db::class,
            'jwt.auth' => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
        ]);

        // 2. API group order (TenantDB first)
        // $middleware->group('api', [
        //     'tenant.db' => \App\Http\Middleware\Dynamic_db::class,
        //     'jwt.auth' => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
        //     // 'tenant.db',       // Switch to tenant connection
        //     // 'jwt.auth',        // Authenticate with JWT
        // ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
