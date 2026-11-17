@extends('admin.layout')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
    <livewire:admin.staff-accounts />
@endsection
