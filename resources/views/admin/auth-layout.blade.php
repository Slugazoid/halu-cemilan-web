<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · Panel admin</title>
    @vite('resources/css/app.css')
</head>
<body class="grid min-h-svh place-items-center bg-zinc-950 px-4 py-8 font-sans text-foreground antialiased">
    <main class="grid w-full max-w-md gap-6 rounded-2xl bg-background p-6 shadow-2xl sm:p-9">
        <p class="flex items-center gap-2.5 font-semibold">
            <span class="grid size-8 place-items-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground"><x-admin.icon name="store" /></span>
            Panel admin
        </p>
        @yield('content')
        <a href="{{ route('home') }}" class="justify-self-start py-2 text-sm text-muted-foreground underline underline-offset-4">Kembali ke situs toko</a>
    </main>
</body>
</html>
