@extends('layouts.app')

@section('title', 'Nouvelles')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-bold text-stone-900 mb-8">Nouvelles</h1>

    @if($posts->isNotEmpty())
        <div class="space-y-8">
            @foreach($posts as $post)
                <article class="bg-white rounded-lg shadow-sm border border-stone-200 overflow-hidden hover:shadow-md transition">
                    <div class="md:flex">
                        @if($post->featured_image)
                            <div class="md:w-64 shrink-0">
                                <a href="{{ route('posts.show', $post->slug) }}">
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}"
                                         class="w-full h-48 object-cover hover:opacity-95 transition">
                                </a>
                            </div>
                        @endif
                        <div class="p-6 flex-1">
                            <h2 class="text-xl font-bold text-stone-900 mb-2">
                                <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-apcq transition">
                                    {{ $post->title }}
                                </a>
                            </h2>
                            <p class="text-stone-600 text-sm mb-3">
                                {{ $post->published_at?->translatedFormat('d F Y') }} — {{ $post->author->name }}
                            </p>
                            @if($post->excerpt)
                                <p class="text-stone-700 mb-4">{{ $post->excerpt }}</p>
                            @else
                                <p class="text-stone-700 mb-4">{{ Str::limit(Str::stripTags($post->content), 200) }}</p>
                            @endif
                            <a href="{{ route('posts.show', $post->slug) }}"
                               class="inline-flex items-center font-medium text-apcq hover:underline">
                                Lire la suite
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $posts->links() }}
        </div>
    @else
        <p class="text-stone-600">Aucun article pour le moment.</p>
    @endif
</div>
@endsection
