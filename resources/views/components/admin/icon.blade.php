@props(['name'])

<svg {{ $attributes->class('size-4 shrink-0') }} viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('dashboard') <rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/> @break
        @case('store') <path d="M3 10l2-6h14l2 6M3 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0M5 13v7h14v-7m-9 7v-5h4v5"/> @break
        @case('bag') <path d="M5 8h14l2 13H3L5 8Zm4 2V6a3 3 0 0 1 6 0v4"/> @break
        @case('users') <circle cx="9" cy="7" r="3"/><path d="M3 20v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2m2-16a3 3 0 0 1 0 6m1 3a5 5 0 0 1 3 5v2"/> @break
        @case('menu') <rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8m-8 4h8m-8 4h5"/> @break
        @case('chart') <path d="M3 3v18h18M8 16v-4m5 4V6m5 10V9"/> @break
        @case('image') <rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5-7 10"/> @break
        @case('tag') <path d="M20 3h-7L3 13l8 8L21 11V4a1 1 0 0 0-1-1Z"/><circle cx="16.5" cy="7.5" r="1"/> @break
        @case('search') <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4.5 4.5"/> @break
        @case('settings') <path d="M4 7h16M4 17h16m-11-3v6m6-16v6"/> @break
        @case('checklist') <rect x="4" y="4" width="16" height="16" rx="2"/><path d="m7 12 3 3 7-7"/> @break
        @case('logout') <path d="M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h5m0-9h11m-4-4 4 4-4 4"/> @break
        @case('external') <path d="M13 5h6v6m0-6-8 8m6 0v6H5V7h6"/> @break
        @case('panel') <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16"/> @break
        @case('close') <path d="m6 6 12 12M18 6 6 18"/> @break
    @endswitch
</svg>
