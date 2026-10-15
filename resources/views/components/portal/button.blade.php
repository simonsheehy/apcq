@props(['variant' => 'primary'])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'text-white ring-1 ring-white/40 hover:ring-apcq hover:text-apcq',
        default => 'bg-white text-portal hover:bg-apcq hover:text-white',
    };
@endphp

<button {{ $attributes->merge(['type' => 'submit', 'class' => "inline-flex w-full items-center justify-center rounded-full px-6 py-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-apcq/60 {$variantClasses}"]) }}>
    {{ $slot }}
</button>
