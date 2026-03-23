@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
<section class="bg-linear-to-br from-slate-800 to-stone-900 text-white">
    <div class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-12 lg:py-20">
        <div class="lg:col-span-7">
            <p class="mb-4 text-xs font-semibold uppercase tracking-[0.22em] text-apcq">Association de l'industrie cinématographique</p>
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Nous unissons et faisons rayonner les cinémas du Québec.</h1>
            <p class="mt-5 max-w-2xl text-base leading-7 text-slate-300">
                L'APCQ rassemble les exploitants de toutes tailles et défend l'expérience salle comme premier choix de divertissement hors domicile.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('members.index') }}" class="rounded-md bg-apcq px-5 py-2.5 text-sm font-semibold text-white hover:bg-apcq-dark transition">Voir les membres</a>
                <a href="{{ route('posts.index') }}" class="rounded-md border border-slate-500 px-5 py-2.5 text-sm font-semibold text-slate-100 hover:bg-slate-800 transition">Nouvelles</a>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-3 lg:col-span-5 lg:grid-cols-1">
            <div class="rounded-lg border border-slate-700 bg-white/5 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Box office 2025</p>
                <p class="mt-2 text-2xl font-bold">8.6 G$</p>
            </div>
            <div class="rounded-lg border border-slate-700 bg-white/5 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Écrans représentés</p>
                <p class="mt-2 text-2xl font-bold">64 000+</p>
            </div>
            <div class="rounded-lg border border-slate-700 bg-white/5 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Réseau</p>
                <p class="mt-2 text-2xl font-bold">80 pays</p>
            </div>
        </div>
    </div>
</section>

<div class="mx-auto w-full max-w-7xl px-4 py-14 sm:px-6">
    @if($featuredPost)
        <section class="mb-14 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:p-8">
            <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
                @if($featuredPost->featured_image)
                    <div class="overflow-hidden rounded-xl lg:col-span-5">
                        <img src="{{ asset('storage/' . $featuredPost->featured_image) }}" alt="{{ $featuredPost->title }}"
                             class="h-64 w-full object-cover lg:h-80">
                    </div>
                @endif
                <div class="{{ $featuredPost->featured_image ? 'lg:col-span-7' : 'lg:col-span-12' }}">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-apcq">À la une</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">
                        <a href="{{ route('posts.show', $featuredPost->slug) }}" class="hover:text-apcq transition">
                            {{ $featuredPost->title }}
                        </a>
                    </h2>
                    <p class="mt-3 text-sm text-slate-500">
                        {{ $featuredPost->published_at?->translatedFormat('d F Y') }}
                    </p>
                    <p class="mt-4 text-slate-700">
                        {{ Str::limit($featuredPost->excerpt ?? Str::stripTags($featuredPost->content), 220) }}
                    </p>
                    <a href="{{ route('posts.show', $featuredPost->slug) }}"
                       class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-apcq hover:underline">
                        Lire la suite
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    <section class="grid gap-8 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <div class="mb-6 flex items-center justify-between gap-3">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Dernières nouvelles</h2>
                <a href="{{ route('posts.index') }}" class="text-sm font-semibold text-apcq hover:underline">Voir tout</a>
            </div>

            @if($recentPosts->isNotEmpty())
                <div class="space-y-3">
                    @foreach($recentPosts as $post)
                        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <a href="{{ route('posts.show', $post->slug) }}" class="text-base font-semibold text-slate-900 hover:text-apcq transition">
                                    {{ $post->title }}
                                </a>
                                <span class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    {{ $post->published_at?->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-slate-600">Aucun article pour le moment.</p>
            @endif
        </div>

        <aside class="space-y-4 lg:col-span-4">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Calendrier</h3>
                    <a href="{{ route('events.index') }}" class="text-xs font-semibold text-apcq hover:underline">Voir tout</a>
                </div>
                @if($upcomingEvents->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($upcomingEvents as $event)
                            <article class="rounded-lg border border-slate-200 p-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-apcq">
                                    {{ $event->starts_at?->translatedFormat('d M Y') }}
                                </p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $event->title }}</p>
                                @if($event->location)
                                    <p class="mt-1 text-xs text-slate-600">{{ $event->location }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-700">Aucun événement à venir pour le moment.</p>
                @endif
            </div>
        </aside>
    </section>
</div>
@endsection
