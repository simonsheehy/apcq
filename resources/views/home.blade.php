@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
@php
    $heroBadge = $homeSettings->home_hero_badge ?: "Association de l'industrie cinématographique";
    $heroTitle = $homeSettings->home_hero_title ?: 'Nous unissons et faisons rayonner les cinémas du Québec.';
    $heroText = $homeSettings->home_hero_text ?: "L'APCQ rassemble les exploitants de toutes tailles et défend l'expérience salle comme premier choix de divertissement hors domicile.";
    $heroBackgroundImage = $homeSettings->home_hero_background_image
        ? asset('storage/' . $homeSettings->home_hero_background_image)
        : asset('images/cinema.jpg');
@endphp
<section class="relative isolate overflow-hidden bg-slate-950 text-white">
    <img
        src="{{ $heroBackgroundImage }}"
        alt=""
        class="pointer-events-none absolute inset-0 h-full w-full scale-105 object-cover object-center blur-md"
        loading="lazy"
    >
    <img
        src="{{ $heroBackgroundImage }}"
        alt=""
        class="pointer-events-none absolute inset-0 h-full w-full object-cover object-center opacity-60"
        loading="lazy"
    >
    <div class="hero-tech-grid pointer-events-none absolute inset-0 opacity-35"></div>

    <div class="relative mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 lg:py-32">
        <div class="max-w-3xl">
            <p class="animate-fade-up mb-4 text-xs font-semibold uppercase tracking-[0.22em] text-cyan-100">
                {{ $heroBadge }}
            </p>
            <h1 class="animate-fade-up-delay text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                {{ $heroTitle }}
            </h1>
            <p class="animate-fade-up-delay-2 mt-5 max-w-2xl text-base leading-7 text-slate-200">
                {{ $heroText }}
            </p>
            <div class="animate-fade-up-delay-3 mt-8 flex flex-wrap gap-3">
                <a href="{{ route('members.index') }}" class="rounded-md bg-apcq px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 hover:-translate-y-0.5 hover:bg-apcq-dark transition">Voir les membres</a>
                <a href="{{ route('posts.index') }}" class="rounded-md border border-slate-400/60 bg-slate-900/40 px-5 py-2.5 text-sm font-semibold text-slate-100 backdrop-blur hover:-translate-y-0.5 hover:bg-slate-800/80 transition">Nouvelles</a>
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
                            <article class="py-3">
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
