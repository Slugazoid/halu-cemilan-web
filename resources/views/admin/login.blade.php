@extends('admin.auth-layout')

@section('title', 'Masuk')
@section('content')
    <div class="grid gap-2">
        <h1 class="text-2xl font-semibold tracking-tight">Masuk ke panel admin</h1>
        <p class="text-sm text-muted-foreground">Gunakan akun Pemilik atau Staf yang sudah disiapkan.</p>
    </div>

    <form action="{{ route('admin.login.store') }}" method="post" class="grid gap-5">
        @csrf
        <x-admin.field name="identity" label="Nomor HP atau email">
            <input id="identity" name="identity" class="admin-input" value="{{ old('identity') }}"
                autocomplete="username" required autofocus maxlength="255">
        </x-admin.field>
        <x-admin.field name="password" label="Kata sandi">
            <input id="password" name="password" type="password" class="admin-input" autocomplete="current-password" required>
        </x-admin.field>
        <button type="submit" class="admin-button admin-button-primary">Masuk</button>
    </form>
@endsection
