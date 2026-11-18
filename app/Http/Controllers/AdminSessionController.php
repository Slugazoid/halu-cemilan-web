<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('admin')->user()?->is_active) {
            return to_route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identity' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $identity = Str::lower($data['identity']);
        $field = str_contains($identity, '@') ? 'email' : 'phone';
        $identity = $field === 'phone' ? Admin::normalizePhone($identity) : $identity;
        $key = 'admin-login:'.hash('sha256', $identity.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['identity' => 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.']);
        }

        $guard = Auth::guard('admin');
        $valid = $guard->attemptWhen(
            [$field => $identity, 'password' => $data['password'], 'is_active' => true],
            fn (Admin $admin): bool => ! $admin->must_change_password || ! $admin->temporary_password_expires_at?->isPast(),
        );

        if (! $valid) {
            RateLimiter::hit($key, 900);

            throw ValidationException::withMessages(['identity' => 'Nomor HP, email, atau kata sandi tidak sesuai.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put('admin.password_hash', hash('sha256', $guard->user()->getAuthPassword()));
        $guard->user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->forget(['url.intended', 'admin.password_hash']);
        $request->session()->regenerate(true);

        return to_route('admin.login');
    }
}
