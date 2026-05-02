<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->company_id) {
            app()->instance('current_company_id', (int) $user->company_id);

            $user->loadMissing('company');
            app()->instance('current_company', $user->company);
        }

        return $next($request);
    }
}