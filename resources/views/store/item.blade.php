@extends('store.layout')

@section('title', $item->name.' · '.$store->name)
@section('description', $item->description ?: ($item->name.' di '.$store->name.'.'))
@section('content')
    <article class="grid max-w-2xl gap-4 rounded-2xl border border-border bg-white p-5 sm:p-8">
        <nav class="text-sm text-muted-foreground" aria-label="Breadcrumb">
            <a class="underline underline-offset-2" href="{{ route('store.catalog') }}">Menu</a> /
            <a class="underline underline-offset-2" href="{{ route('store.category', $item->category->slug) }}">{{ $item->category->name }}</a>
        </nav>
        <h1 class="text-3xl font-semibold tracking-tight">{{ $item->name }}</h1>
        @unless ($item->is_available) <p class="font-medium text-muted-foreground">Habis</p> @endunless
        <p class="text-lg font-semibold">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
        @if ($item->description) <p class="whitespace-pre-line">{{ $item->description }}</p> @endif
        <dl class="grid gap-3 border-t border-border pt-4">
            <div><dt class="font-medium">Berat bersih</dt><dd>{{ $item->weight_grams }} g</dd></div>
            @if ($item->ingredients) <div><dt class="font-medium">Komposisi</dt><dd>{{ $item->ingredients }}</dd></div> @endif
            @if ($item->shelf_life) <div><dt class="font-medium">Masa simpan</dt><dd>{{ $item->shelf_life }}</dd></div> @endif
        </dl>
    </article>
@endsection
