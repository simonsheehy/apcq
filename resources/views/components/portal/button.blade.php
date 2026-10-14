<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex w-full items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-portal transition hover:bg-apcq hover:text-white focus:outline-none focus:ring-2 focus:ring-apcq/60']) }}>
    {{ $slot }}
</button>
