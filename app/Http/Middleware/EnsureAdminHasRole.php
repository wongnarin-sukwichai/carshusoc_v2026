<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasRole
{
    /**
     * Restrict a route to admins whose role is one of the given values.
     * Usage: ->middleware('admin.role:super_admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = $request->user('admin');

        abort_unless($admin && in_array($admin->role, $roles, true), 403);

        return $next($request);
    }
}
