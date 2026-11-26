<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Akun pelanggan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-svh bg-amber-50/40 font-sans text-foreground antialiased">
    <header class="border-b border-border bg-white">
        <div class="mx-auto flex min-h-16 max-w-3xl items-center justify-between gap-4 px-4">
            <a href="{{ route('home') }}" class="font-semibold">Halu Cemilan Instan</a>
            <a href="{{ route('customer.addresses.index') }}" class="admin-button">Alamat saya</a>
        </div>
    </header>
    <main class="mx-auto grid w-full max-w-3xl gap-6 px-4 py-8 sm:py-12">
        @yield('content')
    </main>
</body>
</html>
