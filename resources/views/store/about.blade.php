@extends('store.layout')

@section('title', 'Tentang · '.$store->name)
@section('content')
    <article class="grid max-w-prose gap-5">
        <h1 class="text-3xl font-semibold tracking-tight">Tentang {{ $store->name }}</h1>
        @if ($store->description) <p class="whitespace-pre-line">{{ $store->description }}</p> @endif
        @if ($store->address || $store->maps_url)
            <section class="grid gap-1">
                <h2 class="font-semibold">Lokasi</h2>
                @if ($store->address) <p class="whitespace-pre-line">{{ $store->address }}</p> @endif
                @if ($store->maps_url) <a class="justify-self-start py-2 underline" href="{{ $store->maps_url }}">Buka peta</a> @endif
            </section>
        @endif
        @if ($store->opening_hours) <section><h2 class="font-semibold">Jam buka</h2><p class="whitespace-pre-line">{{ $store->opening_hours }}</p></section> @endif
        @if ($store->shipping_info) <section><h2 class="font-semibold">Pengiriman</h2><p class="whitespace-pre-line">{{ $store->shipping_info }}</p></section> @endif
    </article>
@endsection
