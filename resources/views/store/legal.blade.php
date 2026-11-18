@extends('store.layout')

@section('title', $title.' · '.$store->name)
@section('content')
    <article class="grid max-w-prose gap-4">
        <h1 class="text-3xl font-semibold tracking-tight">{{ $title }}</h1>
        <p class="whitespace-pre-line">{{ $content }}</p>
    </article>
@endsection
