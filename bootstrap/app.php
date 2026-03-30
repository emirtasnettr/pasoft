<?php

use App\Http\Middleware\BlockCompletedInstallWizard;
use App\Http\Middleware\EnsureApplicationInstalled;
use App\Http\Middleware\EnsureCrmAccess;
use App\Http\Middleware\EnsureSystemAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('policies:renewal-reminders')->dailyAt('08:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('web', [
            EnsureApplicationInstalled::class,
        ]);

        $middleware->web(append: [
            SecurityHeadersMiddleware::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'system.admin' => EnsureSystemAdmin::class,
            'crm.access' => EnsureCrmAccess::class,
            'install.open' => BlockCompletedInstallWizard::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
