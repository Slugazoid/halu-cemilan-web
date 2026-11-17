@extends('admin.layout')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
    <x-admin.card>
        <p class="text-sm text-muted-foreground">Modul {{ strtolower($title) }} belum tersedia.</p>
    </x-admin.card>
@endsection
