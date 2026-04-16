@extends('layouts.app')

@section('title', 'Événements')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 py-12 sm:px-6">
    <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-apcq">Calendrier</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Événements</h1>
            <p class="mt-2 text-sm text-slate-600">Retrouvez les prochains rendez-vous de l'APCQ et de l'industrie.</p>
        </div>
        <a href="{{ route('contact') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition">
            Proposer un événement
        </a>
    </div>

    @if($events->isNotEmpty())
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($events as $event)
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    @if($event->featured_image)
                        <a href="{{ route('events.show', $event->slug) }}">
                            <img src="{{ asset('storage/' . $event->featured_image) }}"
                                 alt="{{ $event->title }}"
                                 class="h-48 w-full object-cover">
                        </a>
                    @endif
                    <div class="p-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-apcq">
                        {{ $event->starts_at?->translatedFormat('d F Y') ?? 'Date à confirmer' }}
                    </p>
                    <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900">
                        <a href="{{ route('events.show', $event->slug) }}" class="hover:text-apcq transition">
                            {{ $event->title }}
                        </a>
                    </h2>
                    @if($event->location)
                        <p class="mt-2 text-sm font-medium text-slate-600">{{ $event->location }}</p>
                    @endif
                    @if($event->excerpt)
                        <p class="mt-4 text-sm leading-6 text-slate-700">{{ $event->excerpt }}</p>
                    @elseif($event->description)
                        <div class="prose prose-sm mt-4 max-w-none text-slate-700">{!! \Illuminate\Support\Str::limit((string) $event->description, 220) !!}</div>
                    @endif
                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        <a href="{{ route('events.show', $event->slug) }}" class="inline-flex text-sm font-semibold text-apcq hover:underline">
                            Détails
                        </a>
                    @if($event->event_url)
                        <a href="{{ $event->event_url }}" target="_blank" rel="noopener" class="inline-flex text-sm font-semibold text-apcq hover:underline">
                            En savoir plus
                        </a>
                    @endif
                    </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-8">
            {{ $events->links() }}
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600">
            Aucun événement pour le moment.
        </div>
    @endif
</div>
@endsection
