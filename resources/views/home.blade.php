@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    {{-- Article vedette --}}
    @if($featuredPost)
        <article class="bg-white rounded-lg shadow-sm border border-stone-200 overflow-hidden mb-12">
            <div class="md:flex">
                @if($featuredPost->featured_image)
                    <div class="md:w-1/2">
                        <img src="{{ asset('storage/' . $featuredPost->featured_image) }}" alt="{{ $featuredPost->title }}"
                             class="w-full h-64 md:h-full object-cover">
                    </div>
                @endif
                <div class="p-6 md:p-8 {{ $featuredPost->featured_image ? 'md:w-1/2' : '' }}">
                    <h2 class="text-2xl md:text-3xl font-bold text-stone-900 mb-2">
                        <a href="{{ route('posts.show', $featuredPost->slug) }}" class="hover:text-apcq transition">
                            {{ $featuredPost->title }}
                        </a>
                    </h2>
                    <p class="text-stone-600 text-sm mb-4">
                        {{ $featuredPost->published_at?->translatedFormat('d F Y') }} — {{ $featuredPost->author->name }}
                    </p>
                    <p class="text-stone-700 mb-4">
                        {{ Str::limit($featuredPost->excerpt ?? Str::stripTags($featuredPost->content), 200) }}
                    </p>
                    <a href="{{ route('posts.show', $featuredPost->slug) }}"
                       class="inline-flex items-center font-medium text-apcq hover:underline">
                        Lire la suite
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </article>
    @endif

    {{-- Articles récents --}}
    <section>
        <h2 class="text-xl font-bold text-stone-900 mb-6">Articles récents</h2>
        @if($recentPosts->isNotEmpty())
            <ul class="space-y-4">
                @foreach($recentPosts as $post)
                    <li class="flex flex-col sm:flex-row sm:items-center gap-2 py-4 border-b border-stone-200 last:border-0">
                        <a href="{{ route('posts.show', $post->slug) }}" class="font-medium text-stone-900 hover:text-apcq transition flex-1">
                            {{ $post->title }}
                        </a>
                        <span class="text-sm text-stone-500 sm:shrink-0">
                            {{ $post->published_at?->translatedFormat('d F Y') }}
                        </span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-8">
                <a href="{{ route('posts.index') }}" class="inline-flex items-center font-medium text-apcq hover:underline">
                    Voir toutes les nouvelles
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @else
            <p class="text-stone-600">Aucun article pour le moment.</p>
        @endif
    </section>

    {{-- Calendrier (placeholder) --}}
    <section id="calendrier" class="mt-16 pt-12 border-t border-stone-200">
        <h2 class="text-xl font-bold text-stone-900 mb-6">Calendrier</h2>
        <p class="text-stone-600">Section calendrier à venir.</p>
    </section>
</div>
@endsection
