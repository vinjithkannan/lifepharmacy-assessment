<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TimeoutMiddleware;
use App\Http\Middleware\ValidateHttpStatusCode;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->append(TimeoutMiddleware::class);
        $middleware->append(ValidateHttpStatusCode::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
