@extends('admin.layout')

@section('title', 'Dasboard')

@section('content')
    <div class="grid gap-1">
        <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Dasboard</h1>
        <p class="text-sm text-muted-foreground">Selamat datang, {{ auth('admin')->user()->name }}.</p>
    </div>

    @if (session('status'))
        <p role="status" class="rounded-lg bg-zinc-100 p-3 text-sm">{{ session('status') }}</p>
    @endif

    @can('admin-owner')
        @unless($storePublished ?? false)
            <x-admin.card title="Siapkan toko">
                <p class="text-sm text-muted-foreground">Lengkapi identitas, nomor WhatsApp, dan menu sebelum menerbitkan situs.</p>
                <a href="{{ route('admin.setup') }}" class="admin-button admin-button-primary justify-self-start">Lanjutkan setup</a>
            </x-admin.card>
        @endunless
    @endcan

    <div class="grid gap-4 sm:grid-cols-2">
        <x-admin.card title="Menu">
            <p class="text-sm text-muted-foreground">Kelola kategori, harga, dan ketersediaan produk.</p>
            <a href="{{ route('admin.menu') }}" class="admin-button admin-button-outline justify-self-start">Buka menu</a>
        </x-admin.card>
        @can('admin-owner')
            <x-admin.card title="Akun admin">
                <p class="text-sm text-muted-foreground">Tambahkan staf dan atur akses ke panel.</p>
                <a href="{{ route('admin.akun-admin') }}" class="admin-button admin-button-outline justify-self-start">Kelola akun admin</a>
            </x-admin.card>
        @endcan
    </div>
@endsection
