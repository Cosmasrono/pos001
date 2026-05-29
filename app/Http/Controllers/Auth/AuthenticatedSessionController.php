<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Rules\CaptchaRule;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Bot detection - check user agent
        $userAgent = $request->userAgent() ?? '';
        if ($this->isSuspiciousBot($userAgent)) {
            \Illuminate\Support\Facades\Log::warning('Suspicious bot login attempt', [
                'ip' => $request->ip(),
                'user_agent' => $userAgent,
                'email' => $request->email
            ]);
            return back()->withErrors([
                'email' => 'Access denied. Please use a standard web browser.',
            ])->onlyInput('email');
        }

        $captchaRules = config('services.recaptcha.public_key')
            ? ['required', new CaptchaRule()]
            : ['nullable', new CaptchaRule()];

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'g-recaptcha-response' => $captchaRules,
        ]);
        

        $credentials = $request->only('email', 'password');
        $user = \App\Models\User::where('email', $validated['email'])->first();
        $remember = $request->boolean('remember') || ($user && $user->isOwner());

        if (Auth::attempt($credentials, $remember)) {
            // CHECK 1: Verify email is verified
            if (!Auth::user()->hasVerifiedEmail()) {
                $request->session()->regenerate();
                return redirect()->route('verification.notice')
                    ->with('info', 'Please verify your email address. Use the button below to resend the link.');
            }

            // CHECK 2: Check if the system is deactivated
            if (!\App\Models\Setting::isSystemActive() && !Auth::user()->isOwner()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'The system is currently deactivated. Only the owner can log in.',
                ])->onlyInput('email');
            }

            // Capture guest session ID BEFORE regeneration
            $guestSessionId = $request->session()->getId();
            
            $request->session()->regenerate();

            // Merge guest cart items into user's cart
            app(CartService::class)->mergeGuestCart($guestSessionId, Auth::id());

            $ip = $request->ip();
            $country = Cache::remember("ip_country_{$ip}", 86400, function () use ($ip) {
                try {
                    $geo = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode")->json();
                    if (($geo['status'] ?? '') === 'success') {
                        return ($geo['countryCode'] ?? '') . ' ' . ($geo['country'] ?? '');
                    }
                } catch (\Throwable) {}
                return null;
            });

            Auth::user()->forceFill([
                'last_login_at'      => now(),
                'last_login_ip'      => $ip,
                'last_login_country' => $country,
            ])->save();

            event(new Authenticated('web', Auth::user()));
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Detect suspicious bot patterns from user agent
     */
    private function isSuspiciousBot(string $userAgent): bool
    {
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python',
            'java', 'perl', 'ruby', 'r\s', 'request', 'httpclient', 
            'mechanize', 'libwww', 'urllib', 'aiohttp', 'golang', 'node'
        ];

        $userAgentLower = strtolower($userAgent);
        
        // Check for bot patterns
        foreach ($botPatterns as $pattern) {
            if (preg_match("/{$pattern}/i", $userAgentLower)) {
                return true;
            }
        }

        // Check for missing common browser identifiers
        $hasCommonBrowser = preg_match('/(chrome|firefox|safari|edge|opera|msie)/i', $userAgent);
        if (empty($userAgent) || !$hasCommonBrowser) {
            return true;
        }

        return false;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
