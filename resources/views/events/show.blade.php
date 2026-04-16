@extends('layouts.app')

@section('title', $event->title)

@section('content')
<article class="mx-auto w-full max-w-5xl px-4 py-12 sm:px-6">
    <div class="mb-8">
        <a href="{{ route('events.index') }}" class="inline-flex items-center text-sm font-semibold text-apcq hover:underline">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux événements
        </a>
    </div>

    <header class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-apcq">
            {{ $event->starts_at?->translatedFormat('d F Y') ?? 'Date à confirmer' }}
        </p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">{{ $event->title }}</h1>
        @if($event->location)
            <p class="mt-3 text-sm font-medium text-slate-600">{{ $event->location }}</p>
        @endif
    </header>

    @if($event->featured_image)
        <figure class="mb-8 overflow-hidden rounded-xl border border-slate-200">
            <img
                src="{{ asset('storage/' . $event->featured_image) }}"
                alt="{{ $event->title }}"
                class="h-auto w-full object-cover"
            >
        </figure>
    @endif

    @if($event->excerpt)
        <p class="mb-6 text-lg leading-8 text-slate-700">{{ $event->excerpt }}</p>
    @endif

    @if($event->description)
        <div class="prose prose-stone max-w-none prose-a:text-apcq prose-a:no-underline hover:prose-a:underline">
            {!! $event->description !!}
        </div>
    @endif

    @if($event->event_url)
        <div class="mt-10 border-t border-slate-200 pt-6">
            <a
                href="{{ $event->event_url }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-1 text-sm font-semibold text-apcq hover:underline"
            >
                Lien externe de l'événement
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7m0 0v7m0-7L10 14"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5v14h14"/>
                </svg>
            </a>
        </div>
    @endif
</article>
@endsection
