<div class="flex h-full min-h-0 flex-col gap-4 p-3">
    <a href="{{ route('admin.dashboard') }}" class="admin-nav min-h-12 gap-2.5">
        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
            <x-admin.icon name="store" />
        </span>
        <span class="grid min-w-0">
            <span class="truncate font-semibold">{{ $storeName ?? 'Panel admin' }}</span>
            <span class="text-xs text-muted-foreground">{{ auth('admin')->user()->isOwner() ? 'Pemilik' : 'Staf' }}</span>
        </span>
    </a>

    <nav class="grid min-h-0 flex-1 content-start gap-3 overflow-y-auto" aria-label="Navigasi admin">
        <a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif class="admin-nav">
            <x-admin.icon name="dashboard" /> Dasboard
        </a>
        @foreach (['Operasional', 'Toko'] as $group)
            @if ($group === 'Toko' && Gate::denies('admin-owner'))
                @continue
            @endif
            <div>
                <p class="px-2 py-1 text-xs font-medium text-muted-foreground">{{ $group }}</p>
                @foreach (config('admin.sections') as $section => $page)
                    @continue($page['group'] !== $group || ($section === 'setup' && ($storePublished ?? false)))
                    <a href="{{ route('admin.'.$section) }}" @if(request()->routeIs('admin.'.$section)) aria-current="page" @endif class="admin-nav">
                        <x-admin.icon :name="$page['icon']" /> {{ $page['label'] }}
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>
    <div class="grid gap-1 border-t border-sidebar-border pt-2">
        <a class="admin-nav" href="{{ route('admin.password') }}">Ganti kata sandi</a>
        <form method="post" action="{{ route('admin.logout') }}">
            @csrf
            <button class="admin-nav"><x-admin.icon name="logout" /> Keluar</button>
        </form>
    </div>
</div>
