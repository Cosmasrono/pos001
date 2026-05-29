<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Rules\ActiveEmail;
use App\Rules\CaptchaRule;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: any bot that fills this field is silently rejected
        if ($request->filled('website')) {
            return redirect()->route('register');
        }

        $captchaRules = config('services.recaptcha.public_key')
            ? ['required', new CaptchaRule()]
            : ['nullable', new CaptchaRule()];

        $request->validate([
            'shop_name'          => ['required', 'string', 'max:255'],
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'string', 'lowercase', 'email', 'max:255', new ActiveEmail()],
            'phone'              => ['nullable', 'string', 'max:20'],
            'password'           => ['required', 'confirmed', Rules\Password::defaults()],
            'g-recaptcha-response' => $captchaRules,
        ]);

        // If email exists but was never verified, wipe it so they can start fresh
        $existing = User::where('email', $request->email)->first();
       if ($existing) {
    if ($existing->email_verified_at !== null) {
        return back()->withInput()->withErrors([
            'email' => 'This email is already registered and verified. Please log in instead.',
        ]);
    }
    
    DB::transaction(function () use ($existing, $request) {
        $existingId = $existing->id;

        // Only remove the unverified user record.
        // Do not delete company here: other tenant tables can still reference it
        // (e.g. ai_briefs with a NO ACTION FK), which causes SQLSTATE 23000.
        $existing->delete();

        // If this deleted user was still authenticated in the current session,
        // clear auth/session state to avoid stale foreign key references.
        if (Auth::id() === $existingId) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    });
}

        $user = DB::transaction(function () use ($request) {
            $company = Company::create([
                'name'                => $request->shop_name,
                'slug'                => $this->generateUniqueSlug($request->shop_name),
                'email'               => $request->email,
                'phone'               => $request->phone,
                'is_active'           => true,
                'subscription_status' => 'pending',
                'trial_ends_at'       => null,
                'currency'            => 'KES',
                'timezone'            => 'Africa/Nairobi',
                'country'             => 'KE',
                'mpesa_environment'   => 'sandbox',
            ]);

            $user = User::create([
                'company_id' => $company->id,
                'name'       => $request->name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'password'   => Hash::make($request->password),
                'is_active'  => true,
            ]);

            $company->update(['owner_id' => $user->id]);

            $ownerRole = Role::firstOrCreate(
                ['name' => 'owner'],
                ['display_name' => 'System Owner', 'description' => 'Ultimate system authority and control']
            );
            $user->roles()->attach($ownerRole->id);

            $branch = Branch::create([
                'company_id'                     => $company->id,
                'name'                          => 'Main Branch',
                'code'                          => 'MAIN-' . strtoupper(Str::random(4)),
                'address'                       => null,
                'phone'                         => $request->phone,
                'is_active'                     => true,
                'is_main'                       => true,
                'owner_id'                      => $user->id,
                'stock_distribution_percentage' => 100.00,
            ]);

            $user->update(['branch_id' => $branch->id]);

            return $user;
        });

        event(new Registered($user));

        return redirect()
            ->route('verification.notice.guest')
            ->with('success', "Registration successful! Please check your email to verify your account. Your 7-day free trial will begin after email verification.");
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'shop';
        $slug = $base;
        $i = 1;
        while (Company::where('slug', $slug)->exists()) {
            $i++;
            $slug = "{$base}-{$i}";
        }
        return $slug;
    }
}
