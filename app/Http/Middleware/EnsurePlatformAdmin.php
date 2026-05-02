<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to platform-owner-only routes.
 * Reads admin email list from PLATFORM_ADMIN_EMAILS in .env.
 */
class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isPlatformAdmin()) {
            abort(403, 'Platform administrator access required.');
        }

        return $next($request);
    }
}