<?php

use App\Models\Admin;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:create-owner {name} {phone} {--email=}', function (): int {
    if (Admin::query()->where('owner_slot', true)->exists()) {
        $this->error('Akun Pemilik sudah ada.');

        return 1;
    }

    $data = Validator::make([
        'name' => $this->argument('name'),
        'phone' => Admin::normalizePhone($this->argument('phone')),
        'email' => Str::lower($this->option('email') ?? '') ?: null,
        'password' => $this->secret('Kata sandi sementara'),
    ], [
        'name' => ['required', 'string', 'min:2', 'max:50'],
        'phone' => ['required', 'regex:/^628\d{7,12}$/', 'unique:admins,phone'],
        'email' => ['nullable', 'email', 'max:255', 'unique:admins,email'],
        'password' => ['required', 'max:72', Password::min(8)->letters()->numbers()],
    ])->validate();

    (new Admin($data))->forceFill([
        'role' => 'owner',
        'owner_slot' => true,
        'temporary_password_expires_at' => now()->addDay(),
    ])->save();

    $this->info('Akun Pemilik dibuat. Masuk melalui /admin/masuk untuk mengganti kata sandi.');

    return 0;
})->purpose('Create the store owner account');
