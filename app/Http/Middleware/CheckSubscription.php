<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->isPlatformAdmin()) {
            return $next($request);
        }

        $allowedRoutes = ['subscription.expired', 'login', 'logout', 'system.unavailable'];
        if (in_array($request->route()?->getName(), $allowedRoutes, true)) {
            return $next($request);
        }

        $company = $user->company;

        if (!$company) {
            return redirect()->route('subscription.expired');
        }

        // Auto-suspend if trial has expired but DB hasn't been updated yet
        if ($company->subscription_status === 'trial'
            && $company->trial_ends_at
            && Carbon::now()->greaterThan($company->trial_ends_at)
        ) {
            $company->update(['subscription_status' => 'suspended']);
            $company->subscription_status = 'suspended';
        }

        if (!$company->canAccessSystem()) {
            return redirect()->route('subscription.expired');
        }

        return $next($request);
    }
}
