@extends('admin.layout')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
    @if (session('status'))
        <p role="status" class="rounded-lg bg-zinc-100 p-3 text-sm">{{ session('status') }}</p>
    @endif
    <livewire:admin.store-settings :$section />
@endsection
