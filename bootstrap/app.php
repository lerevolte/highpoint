<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // $middleware->validateCsrfTokens(except: [
        //     'collect',
        //     'init/*',
        //     'pixel/*'
        // ]);
        $middleware->statefulApi();
        $middleware->alias([
            'project.permission' => \App\Http\Middleware\CheckProjectPermissions::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->call(function () {
            \Artisan::call('yandex-metrika:refresh-tokens');
        })->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
