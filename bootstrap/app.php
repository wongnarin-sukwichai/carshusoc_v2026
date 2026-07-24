<?php

use App\Http\Middleware\EnsureAdminHasRole;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin.role' => EnsureAdminHasRole::class,
        ]);

        // Neither guard has a standalone login page any more — both login via
        // modals on the public home page — so any guest hitting a protected
        // route (user or admin) lands there instead.
        $middleware->redirectGuestsTo(fn () => route('home'));

        // The "admins" table is a separate guard from "users" (see config/auth.php),
        // so authenticated redirects still need to branch by which area was requested.
        $middleware->redirectUsersTo(fn (Request $request) => $request->is('admin*') ? route('admin.dashboard') : route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
