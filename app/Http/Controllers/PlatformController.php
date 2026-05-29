<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
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

        $recentLogins = User::whereNotNull('last_login_at')
            ->orderByDesc('last_login_at')
            ->limit(20)
            ->get(['id', 'name', 'email', 'last_login_at', 'last_login_ip', 'last_login_country', 'company_id'])
            ->map(fn ($u) => [
                'name'         => $u->name,
                'email'        => $u->email,
                'last_login_at'=> $u->last_login_at,
                'ip'           => $u->last_login_ip ?? '—',
                'country'      => $u->last_login_country ?? '—',
            ]);

        return view('platform.index', compact(
            'usersActive24h', 'usersActive7d', 'usersActive30d',
            'usersAccountActive', 'usersTotal',
            'companiesTotal', 'companiesOnTrial', 'companiesActive',
            'companiesExpired', 'companiesInactive',
            'companies', 'recentSignups', 'recentLogins'
        ));
    }

    public function activateCompany(Company $company): RedirectResponse
    {
        $expiresAt = Carbon::now()->addYear()->endOfDay();

        $company->update([
            'subscription_status'    => 'active',
            'is_active'              => true,
            'subscription_expires_at'=> $expiresAt,
        ]);

        return back()->with('success', "{$company->name} activated for 1 year until " . $expiresAt->format('d M Y') . '.');
    }

    public function suspendCompany(Company $company): RedirectResponse
    {
        $company->update(['subscription_status' => 'suspended']);

        return back()->with('success', "{$company->name} has been suspended.");
    }

   public function purgeBots(): RedirectResponse
{
    // Companies whose owner never logged in, no products, older than 1 hour
    $bots = Company::whereHas('owner', fn($q) => $q->whereNull('last_login_at'))
        ->doesntHave('products')
        ->where('created_at', '<', Carbon::now()->subHour())
        ->with('owner')
        ->get();

    $count = 0;

    foreach ($bots as $company) {
        $id = $company->id;

        \DB::transaction(function () use ($id) {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');

            try {
                $branchIds = \DB::table('branches')->where('company_id', $id)->pluck('id');
                $saleIds   = \DB::table('sales')->where('company_id', $id)->pluck('id');
                $userIds   = \DB::table('users')->where('company_id', $id)->pluck('id');

                \DB::table('product_branch_stocks')->whereIn('branch_id', $branchIds)->delete();

                \DB::table('sale_items')->whereIn('sale_id', $saleIds)->delete();
                \DB::table('sales')->where('company_id', $id)->delete();

                \DB::table('products')->where('company_id', $id)->delete();
                \DB::table('branches')->where('company_id', $id)->delete();

                \DB::table('role_user')->whereIn('user_id', $userIds)->delete();
                \DB::table('users')->where('company_id', $id)->delete();

                \DB::table('companies')->where('id', $id)->delete();
            } finally {
                \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        });

        $count++;
    }

    return back()->with('success', "Purged {$count} bot registration(s).");
}
}
