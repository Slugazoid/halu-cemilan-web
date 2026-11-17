@extends('store.layout')

@section('title', ($category?->name ?? 'Menu').' · '.$store->name)
@section('content')
    <div class="grid gap-5">
        @if ($category)
            <nav class="text-sm text-muted-foreground" aria-label="Breadcrumb">
                <a class="underline underline-offset-2" href="{{ route('store.catalog') }}">Menu</a> / {{ $category->name }}
            </nav>
        @endif
        <h1 class="text-3xl font-semibold tracking-tight">{{ $category?->name ?? 'Menu' }}</h1>
        <form action="{{ url()->current() }}" class="flex max-w-md gap-2" role="search">
            <label for="cari" class="sr-only">Cari nama menu</label>
            <input id="cari" name="cari" type="search" value="{{ $search }}" class="admin-input" placeholder="Cari nama menu">
            <button class="admin-button admin-button-primary">Cari</button>
        </form>
        @if ($categories->isNotEmpty())
            <nav class="sticky top-0 flex gap-2 overflow-x-auto border-b border-border bg-white py-2" aria-label="Kategori menu">
                <a class="admin-button admin-button-outline shrink-0 aria-[current=page]:bg-zinc-100" href="{{ route('store.catalog') }}" @unless($category) aria-current="page" @endunless>Semua</a>
                @foreach ($categories as $entry)
                    <a class="admin-button admin-button-outline shrink-0 aria-[current=page]:bg-zinc-100" href="{{ route('store.category', $entry->slug) }}" @if($entry->is($category)) aria-current="page" @endif>{{ $entry->name }}</a>
                @endforeach
            </nav>
        @endif
        @if ($items->isEmpty())
            <p class="text-muted-foreground">Menu tidak ditemukan.</p>
            @if ($search !== '') <a class="justify-self-start py-2 underline" href="{{ url()->current() }}">Hapus pencarian</a> @endif
        @else
            @foreach ($itemGroups as $categoryItems)
                <section class="grid gap-3">
                    @unless ($category)
                        <h2 class="text-xl font-semibold">{{ $categoryItems->first()->category->name }}</h2>
                    @endunless
                    <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($categoryItems as $item)
                            <x-store.menu-card :$item />
                        @endforeach
                    </ul>
                </section>
            @endforeach
            {{ $items->links() }}
        @endif
    </div>
@endsection
