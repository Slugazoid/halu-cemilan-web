@props(['name', 'label'])

<div {{ $attributes->class('grid min-w-0 gap-1.5') }}>
    <label for="{{ $name }}" class="text-sm font-medium">{{ $label }}</label>
    {{ $slot }}
    @error($name)
        <p class="text-sm text-destructive" role="alert">{{ $message }}</p>
    @enderror
</div>
