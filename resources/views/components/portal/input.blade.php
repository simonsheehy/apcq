@props(['name', 'label', 'type' => 'text', 'id' => null, 'errorBag' => null])

@php($inputId = $id ?? $name)

<div>
    <label for="{{ $inputId }}" class="block text-sm font-semibold">{{ $label }}</label>
    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if($type !== 'password') value="{{ old($name, $attributes->get('value')) }}" @endif
        {{ $attributes->except('value')->merge(['class' => 'mt-2 block w-full rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-white placeholder-white/40 outline-none transition focus:border-apcq focus:ring-2 focus:ring-apcq/40']) }}
        @error($name) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @enderror
    >
    @error($name)
        <p id="{{ $inputId }}-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>
    @enderror
</div>
