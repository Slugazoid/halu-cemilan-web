@props(['item'])

<li @class(['rounded-xl border border-border p-4', 'bg-white' => $item->is_available, 'bg-zinc-100' => ! $item->is_available])>
    <a href="{{ route('store.item', $item->slug) }}" class="grid min-h-11 grid-cols-[auto_1fr] items-start gap-3 hover:underline">
        <span class="grid size-11 place-items-center rounded-lg bg-zinc-100 font-semibold" aria-hidden="true">{{ mb_substr($item->name, 0, 1) }}</span>
        <span class="grid min-w-0 gap-1">
            <span class="break-words font-semibold">{{ $item->name }}</span>
            <span class="text-sm text-muted-foreground">Rp {{ number_format($item->price, 0, ',', '.') }} · {{ $item->weight_grams }} g</span>
            @unless ($item->is_available) <span class="text-sm font-medium">Habis</span> @endunless
        </span>
    </a>
</li>
