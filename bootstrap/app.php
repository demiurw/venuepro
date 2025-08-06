<?php

use App\Http\Middleware\DashboardRedirectMiddleware;
use App\Http\Middleware\EnsureUserStatusMiddleware;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RoleAccessMiddleware;
use App\Http\Middleware\SetTenantFromUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'tenant' => SetTenantFromUser::class,
            'dashboard.redirect' => DashboardRedirectMiddleware::class,
            'role.access' => RoleAccessMiddleware::class,
            'user.status' => EnsureUserStatusMiddleware::class,
        ]);
    })
    ->withProviders([
        // This is now in the correct place
        Spatie\Multitenancy\MultitenancyServiceProvider::class,
        App\Providers\RepositoryServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

