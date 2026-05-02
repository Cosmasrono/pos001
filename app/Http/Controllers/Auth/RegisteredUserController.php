<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
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
        $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $company = Company::create([
                'name'                => $request->shop_name,
                'slug'                => $this->generateUniqueSlug($request->shop_name),
                'email'               => $request->email,
                'phone'               => $request->phone,
                'is_active'           => true,
                'subscription_status' => 'trial',
                'trial_ends_at'       => now()->addDays(7),
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

            $ownerRole = Role::where('name', 'owner')->first();
            if ($ownerRole) {
                $user->roles()->attach($ownerRole->id);
            }

            $branch = Branch::create([
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

        Auth::login($user);

        return redirect()
            ->route('dashboard')
            ->with('success', "Welcome to WingPOS! Your 7-day free trial has started.");
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