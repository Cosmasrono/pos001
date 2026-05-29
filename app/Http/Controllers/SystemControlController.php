<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SystemControlController extends Controller
{
    public function index(): View
    {
        $this->authorizeOwner();
        $isSystemActive        = Setting::isSystemActive();
        $subscriptionStatus    = Setting::get('subscription_status', 'active');
        $subscriptionExpiresAt = Setting::getSubscriptionExpiryDate();

        $suspendedCompanies = Company::with('owner')
            ->whereIn('subscription_status', ['suspended', 'expired'])
            ->orWhere('is_active', false)
            ->get();

        $activeCompanies = Company::with('owner')
            ->whereIn('subscription_status', ['active', 'trial'])
            ->where('is_active', true)
            ->get();

        return view('system_control.index', compact(
            'isSystemActive', 'subscriptionStatus', 'subscriptionExpiresAt',
            'suspendedCompanies', 'activeCompanies'
        ));
    }

    public function manageCompany(Request $request): RedirectResponse
    {
        $this->authorizeOwner();

        $request->validate([
            'company_id'  => ['required', 'exists:companies,id'],
            'action'      => ['required', 'in:activate,suspend'],
            'period'      => ['required_if:action,activate', 'nullable', 'in:30,90,180,365,custom'],
            'custom_date' => ['required_if:period,custom', 'nullable', 'date', 'after:today'],
        ]);

        $company = Company::findOrFail($request->company_id);

        if ($request->action === 'activate') {
            $expiresAt = match ($request->period) {
                '30'     => Carbon::now()->addDays(30)->endOfDay(),
                '90'     => Carbon::now()->addDays(90)->endOfDay(),
                '180'    => Carbon::now()->addDays(180)->endOfDay(),
                '365'    => Carbon::now()->addYear()->endOfDay(),
                'custom' => Carbon::parse($request->custom_date)->endOfDay(),
                default  => Carbon::now()->addYear()->endOfDay(),
            };

            $company->update([
                'is_active'               => true,
                'subscription_status'     => 'active',
                'subscription_expires_at' => $expiresAt,
            ]);

            \App\Models\AuditLog::create([
                'user_id'    => auth()->id(),
                'event'      => 'company_activated',
                'new_values' => ['company' => $company->name, 'expires_at' => $expiresAt->toDateString()],
                'ip_address' => $request->ip(),
            ]);

            return back()->with('success', "{$company->name} activated until {$expiresAt->format('d M Y')}.");
        }

        $company->update(['subscription_status' => 'suspended', 'is_active' => false]);

        \App\Models\AuditLog::create([
            'user_id'    => auth()->id(),
            'event'      => 'company_suspended',
            'new_values' => ['company' => $company->name],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('warning', "{$company->name} has been suspended.");
    }

    public function toggle(Request $request): RedirectResponse
    {
        $this->authorizeOwner();
        
        $newStatus = $request->input('status') === 'activate' ? 'true' : 'false';
        
        Setting::set('system_active', $newStatus);

        // Log the action for security
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $newStatus === 'true' ? 'system_activated' : 'system_deactivated',
            'new_values' => ['message' => "System " . ($newStatus === 'true' ? 'activated' : 'deactivated') . " by " . auth()->user()->name],
            'ip_address' => $request->ip(),
        ]);

        $message = $newStatus === 'true' ? 'System successfully activated.' : 'System successfully deactivated.';
        $type = $newStatus === 'true' ? 'success' : 'warning';

        return redirect()->back()->with($type, $message);
    }

    public function updateSubscription(Request $request): RedirectResponse
    {
        $this->authorizeOwner();
        
        $request->validate([
            'expires_at' => 'required|date',
            'status' => 'required|in:active,expired',
        ]);

        Setting::set('subscription_expires_at', $request->expires_at);
        Setting::set('subscription_status', $request->status);

        return redirect()->back()->with('success', 'Subscription settings updated successfully.');
    }

    protected function authorizeOwner()
    {
        if (!auth()->user() || !auth()->user()->isPlatformAdmin()) {
            abort(403, 'Unauthorized action. Only the Platform Administrator can access this page.');
        }
    }
}
