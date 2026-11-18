<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('admin');
        $admin = $guard->user();

        $sessionHash = $request->session()->get('admin.password_hash');
        $passwordChanged = $admin && $sessionHash && ! hash_equals($sessionHash, hash('sha256', $admin->getAuthPassword()));

        if (! $admin?->is_active || $passwordChanged || ($admin->must_change_password && $admin->temporary_password_expires_at?->isPast())) {
            if ($admin) {
                $guard->logout();
                $request->session()->forget('admin.password_hash');
                $request->session()->regenerate(true);
            }

            abort_if($request->expectsJson(), 401);

            return redirect()->guest(route('admin.login'));
        }

        if ($admin->must_change_password && ! $request->routeIs('admin.password', 'admin.password.update', 'admin.logout')) {
            return to_route('admin.password');
        }

        Auth::shouldUse('admin');

        return $next($request);
    }
}
