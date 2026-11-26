@extends('customer.layout')

@section('title', 'Alamat pelanggan')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-muted-foreground">Profil pelanggan</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Alamat tersimpan</h1>
        </div>
        @if ($addresses->count() < 5)
            <a href="{{ route('customer.addresses.create') }}" class="admin-button admin-button-primary">Tambah alamat</a>
        @endif
    </div>

    @if (session('status'))
        <p role="status" class="rounded-lg border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-900">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div role="alert" class="rounded-lg border border-destructive/40 bg-red-50 p-3 text-sm text-destructive">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @forelse ($addresses as $address)
        <article class="grid gap-4 rounded-xl border border-border bg-white p-4 sm:p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-semibold">{{ $address->label }}</h2>
                        @if ($address->is_primary)
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-900">Alamat utama</span>
                        @endif
                    </div>
                    <p class="mt-1">{{ $address->recipient_name }} · {{ $address->recipient_phone }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('customer.addresses.edit', $address) }}" class="admin-button admin-button-outline">Ubah</a>
                    @unless ($address->is_primary)
                        <form action="{{ route('customer.addresses.primary', $address) }}" method="post">
                            @csrf
                            @method('PATCH')
                            <button class="admin-button admin-button-outline">Jadikan utama</button>
                        </form>
                    @endunless
                    <form action="{{ route('customer.addresses.destroy', $address) }}" method="post" onsubmit="return confirm('Hapus alamat ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="admin-button admin-button-outline text-destructive">Hapus</button>
                    </form>
                </div>
            </div>
            <div class="grid gap-1 text-sm text-muted-foreground">
                <p class="whitespace-pre-line">{{ $address->street_address }}</p>
                <p>{{ $address->village?->name }}, {{ $address->village?->district?->name }}, {{ $address->village?->district?->regency?->name }}, {{ $address->village?->district?->regency?->province?->name }} {{ $address->postal_code }}</p>
                @if ($address->landmark)
                    <p>Patokan: {{ $address->landmark }}</p>
                @endif
                @if ($address->latitude !== null && $address->longitude !== null)
                    <a class="justify-self-start underline underline-offset-4" href="https://maps.google.com/?q={{ $address->latitude }},{{ $address->longitude }}" target="_blank" rel="noopener noreferrer">Lihat pin peta</a>
                @endif
            </div>
        </article>
    @empty
        <div class="rounded-xl border border-dashed border-border bg-white p-6 text-center">
            <p>Belum ada alamat tersimpan.</p>
            <a href="{{ route('customer.addresses.create') }}" class="admin-button admin-button-primary mt-4">Tambah alamat pertama</a>
        </div>
    @endforelse
@endsection
