<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AdminPasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'confirmed', 'different:current_password', 'max:72', Password::min(8)->letters()->numbers()],
        ]);

        $admin = Auth::guard('admin')->user();
        $admin->forceFill([
            'password' => $data['password'],
            'must_change_password' => false,
            'temporary_password_expires_at' => null,
        ])->save();

        $request->session()->regenerate(true);
        $request->session()->put('admin.password_hash', hash('sha256', $admin->getAuthPassword()));

        return to_route('admin.dashboard');
    }
}
