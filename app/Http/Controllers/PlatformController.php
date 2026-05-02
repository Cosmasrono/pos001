<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PlatformController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $usersActive24h     = User::where('last_login_at', '>=', $now->copy()->subDay())->count();
        $usersActive7d      = User::where('last_login_at', '>=', $now->copy()->subDays(7))->count();
        $usersActive30d     = User::where('last_login_at', '>=', $now->copy()->subDays(30))->count();
        $usersAccountActive = User::where('is_active', true)->count();
        $usersTotal         = User::count();
        $companiesTotal    = Company::count();
        $companiesOnTrial  = Company::where('subscription_status', 'trial')->count();
        $companiesActive   = Company::where('subscription_status', 'active')->count();
        $companiesExpired  = Company::whereIn('subscription_status', ['expired', 'suspended'])->count();
        $companiesInactive = Company::where('is_active', false)->count();

        $companies = Company::query()
            ->withCount(['users', 'branches'])
            ->with('owner')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($company) {
                $lastActivity = User::where('company_id', $company->id)
                    ->whereNotNull('last_login_at')
                    ->max('last_login_at');
                return [
                    'id'                  => $company->id,
                    'name'                => $company->name,
                    'slug'                => $company->slug,
                    'owner_name'          => $company->owner?->name ?? '—',
                    'owner_email'         => $company->owner?->email ?? '—',
                    'users_count'         => $company->users_count,
                    'branches_count'      => $company->branches_count,
                    'subscription_status' => $company->subscription_status,
                    'is_active'           => $company->is_active,
                    'trial_ends_at'       => $company->trial_ends_at,
                    'expires_at'          => $company->subscription_expires_at,
                    'created_at'          => $company->created_at,
                    'last_activity'       => $lastActivity ? Carbon::parse($lastActivity) : null,
                ];
            });
        $recentSignups = Company::with('owner')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('platform.index', compact(
            'usersActive24h', 'usersActive7d', 'usersActive30d',
            'usersAccountActive', 'usersTotal',
            'companiesTotal', 'companiesOnTrial', 'companiesActive',
            'companiesExpired', 'companiesInactive',
            'companies', 'recentSignups'
        ));
    }

    public function activateCompany(Company $company, Request $request): RedirectResponse
    {
        $request->validate([
            'expires_at' => 'required|date|after:today',
        ]);

        $company->update([
            'subscription_status'    => 'active',
            'is_active'              => true,
            'subscription_expires_at'=> Carbon::parse($request->expires_at)->endOfDay(),
        ]);

        return back()->with('success', "{$company->name} activated until " . Carbon::parse($request->expires_at)->format('d M Y') . '.');
    }

    public function suspendCompany(Company $company): RedirectResponse
    {
        $company->update(['subscription_status' => 'suspended']);

        return back()->with('success', "{$company->name} has been suspended.");
    }
    
}
