<!DOCTYPE html>
<html lang="id" style="--store-primary: {{ $store->primary_color }}; --store-accent: {{ $store->accent_color }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $store->meta_title ?: ($store->name ?: 'Segera hadir'))</title>
    <meta name="description" content="@yield('description', $store->meta_description ?: ($store->tagline ?: 'Informasi dan menu toko.'))">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($store->is_published)
        <meta property="og:site_name" content="{{ $store->name }}">
        <meta property="og:title" content="@yield('title', $store->meta_title ?: $store->name)">
        <meta property="og:description" content="@yield('description', $store->meta_description ?: ($store->tagline ?: 'Informasi dan menu toko.'))">
        <meta property="og:url" content="{{ url()->current() }}">
        @if ($store->gsc_verification)
            <meta name="google-site-verification" content="{{ $store->gsc_verification }}">
        @endif
    @else
        <meta name="robots" content="noindex, nofollow">
    @endif
    @vite('resources/css/app.css')
</head>
<body @class([
    'grid min-h-svh grid-rows-[auto_1fr_auto] font-sans text-foreground antialiased',
    'bg-white' => $store->theme === 'netral', 'bg-amber-50/40' => $store->theme === 'hangat',
    'bg-zinc-100' => $store->theme === 'kontras',
])>
    <header class="border-b border-border bg-white">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-x-4 px-4 py-2">
            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center gap-2 font-semibold">
                <span class="size-3 rounded-full bg-(--store-primary)" aria-hidden="true"></span>
                {{ $store->name ?: 'Toko' }}
            </a>
            @if ($store->is_published)
                <nav class="flex flex-wrap" aria-label="Navigasi utama">
                    <a class="admin-button" href="{{ route('store.about') }}">Tentang</a>
                    <a class="admin-button" href="{{ route('store.catalog') }}">Menu</a>
                </nav>
            @endif
        </div>
    </header>
    <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:py-14">
        @yield('content')
    </main>
    <footer class="border-t border-border">
        <div class="mx-auto grid max-w-5xl gap-2 px-4 py-6 text-sm text-muted-foreground">
            @if ($store->name) <p class="font-medium text-foreground">{{ $store->name }}</p> @endif
            @if ($store->address) <p>{{ $store->address }}</p> @endif
            @if ($store->whatsapp_number)
                <a class="justify-self-start underline underline-offset-4" href="https://wa.me/{{ $store->whatsapp_number }}">WhatsApp +{{ $store->whatsapp_number }}</a>
            @endif
            @if ($store->is_published)
                <nav class="flex flex-wrap gap-x-4 gap-y-2" aria-label="Navigasi footer">
                    <a class="py-2 underline underline-offset-4" href="{{ route('home') }}">Beranda</a>
                    <a class="py-2 underline underline-offset-4" href="{{ route('store.about') }}">Tentang</a>
                    <a class="py-2 underline underline-offset-4" href="{{ route('store.catalog') }}">Menu</a>
                    @if ($store->privacy_policy) <a class="py-2 underline underline-offset-4" href="{{ route('store.privacy') }}">Kebijakan Privasi</a> @endif
                    @if ($store->terms) <a class="py-2 underline underline-offset-4" href="{{ route('store.terms') }}">Syarat & Ketentuan</a> @endif
                </nav>
            @endif
        </div>
    </footer>
</body>
</html>
