@extends('store.layout')

@section('content')
    <div class="grid gap-5 rounded-2xl border border-border border-t-4 border-t-(--store-accent) bg-white px-5 py-10 sm:px-10 sm:py-16">
        @if ($store->is_published)
            <h1 class="max-w-2xl text-4xl font-semibold tracking-tight sm:text-6xl">{{ $store->name }}</h1>
            @if ($store->tagline) <p class="max-w-xl text-lg text-muted-foreground">{{ $store->tagline }}</p> @endif
            <a href="{{ route('store.catalog') }}" class="admin-button admin-button-primary justify-self-start">Lihat menu</a>
        @else
            <h1 class="text-4xl font-semibold tracking-tight">Segera hadir</h1>
            @if ($store->tagline) <p class="text-lg text-muted-foreground">{{ $store->tagline }}</p> @endif
            @if ($store->whatsapp_number)
                <a href="https://wa.me/{{ $store->whatsapp_number }}" class="admin-button admin-button-primary justify-self-start">Hubungi lewat WhatsApp</a>
            @endif
        @endif
    </div>

    @if ($store->is_published && $store->description)
        <section class="mt-8 grid gap-2">
            <h2 class="text-xl font-semibold">Tentang kami</h2>
            <p class="max-w-prose whitespace-pre-line">{{ $store->description }}</p>
        </section>
    @endif
    @if ($featured->isNotEmpty())
        <section class="mt-8 grid gap-3">
            <h2 class="text-xl font-semibold">Menu unggulan</h2>
            <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $item)
                    <x-store.menu-card :$item />
                @endforeach
            </ul>
        </section>
    @endif
@endsection
