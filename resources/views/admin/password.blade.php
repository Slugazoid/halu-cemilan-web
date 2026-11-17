@extends('admin.auth-layout')

@section('title', 'Ganti kata sandi')
@section('content')
    <div class="grid gap-2">
        <h1 class="text-2xl font-semibold tracking-tight">Ganti kata sandi</h1>
        @if (auth('admin')->user()->must_change_password)
            <p class="text-sm text-muted-foreground">Ganti kata sandi sementara untuk membuka panel admin.</p>
        @endif
    </div>
    <form action="{{ route('admin.password.update') }}" method="post" class="grid gap-5">
        @csrf
        @method('PUT')
        <x-admin.field name="current_password" label="Kata sandi saat ini">
            <input id="current_password" name="current_password" type="password" class="admin-input" autocomplete="current-password" required>
        </x-admin.field>
        <x-admin.field name="password" label="Kata sandi baru">
            <input id="password" name="password" type="password" class="admin-input" autocomplete="new-password" minlength="8" maxlength="72" required>
        </x-admin.field>
        <x-admin.field name="password_confirmation" label="Ulangi kata sandi baru">
            <input id="password_confirmation" name="password_confirmation" type="password" class="admin-input" autocomplete="new-password" required>
        </x-admin.field>
        <button class="admin-button admin-button-primary">Simpan kata sandi</button>
    </form>
    <form action="{{ route('admin.logout') }}" method="post">
        @csrf
        <button class="admin-button admin-button-outline w-full">Keluar</button>
    </form>
@endsection
