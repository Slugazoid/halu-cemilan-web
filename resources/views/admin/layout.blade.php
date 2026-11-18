<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', $title ?? 'Dasbor') · {{ $storeName ?? 'Panel admin' }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="min-h-svh bg-background font-sans text-foreground antialiased">
    <div x-data="{ sidebarOpen: true }" class="flex min-h-svh">
        <aside x-show="sidebarOpen" class="sticky top-0 hidden h-svh w-64 shrink-0 border-r border-sidebar-border bg-sidebar text-sidebar-foreground md:block" id="admin-sidebar">
            @include('admin.sidebar')
        </aside>

        <dialog x-ref="navigation" aria-label="Navigasi admin"
            @click.self="$refs.navigation.close()"
            class="fixed inset-y-0 left-0 m-0 h-svh max-h-none w-[min(18rem,85vw)] max-w-none border-0 bg-sidebar p-0 text-sidebar-foreground shadow-xl backdrop:bg-black/50 md:hidden">
            <div class="relative h-full pt-8">
                <button type="button" class="admin-button absolute top-0 right-1" @click="$refs.navigation.close()" aria-label="Tutup navigasi">
                    <x-admin.icon name="close" />
                </button>
                @include('admin.sidebar')
            </div>
        </dialog>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-10 flex min-h-14 items-center gap-2 border-b border-border bg-background px-3 sm:px-5">
                <button type="button" class="admin-button md:hidden" @click="$refs.navigation.showModal()" aria-label="Buka navigasi">
                    <x-admin.icon name="panel" />
                </button>
                <button type="button" class="admin-button hidden md:inline-flex" @click="sidebarOpen = !sidebarOpen"
                    :aria-expanded="sidebarOpen" aria-controls="admin-sidebar" aria-label="Tampilkan atau sembunyikan navigasi">
                    <x-admin.icon name="panel" />
                </button>
                <span class="truncate text-sm font-medium">@yield('title', $title ?? 'Dasbor')</span>
                <a href="{{ route('home') }}" class="admin-button ml-auto shrink-0">
                    Lihat situs <x-admin.icon name="external" />
                </a>
            </header>

            <main class="mx-auto grid max-w-6xl gap-6 px-4 py-6 sm:px-6 sm:py-8">
                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
