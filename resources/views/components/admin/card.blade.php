@props(['title' => null])

<section {{ $attributes->class('grid gap-4 rounded-xl border border-border bg-card p-5 text-card-foreground shadow-xs sm:p-6') }}>
    @if ($title)
        <h2 class="font-semibold leading-tight">{{ $title }}</h2>
    @endif
    {{ $slot }}
</section>
