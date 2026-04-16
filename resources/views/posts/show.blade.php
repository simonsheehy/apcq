@extends('layouts.app')

@section('title', $post->title)

@section('content')
@php
    $galleryImages = collect($post->gallery_images ?? [])
        ->filter(fn ($imagePath) => is_string($imagePath) && $imagePath !== '')
        ->values();
@endphp
<article class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <header class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-stone-900 mb-4">{{ $post->title }}</h1>
        <p class="text-stone-600">
            {{ $post->published_at?->translatedFormat('d F Y') }}
        </p>
        @if($post->tags->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                    <a href="{{ route('posts.by-tag', $tag->slug) }}"
                       class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-apcq/10 hover:text-apcq transition">
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </header>

    @if($post->featured_image)
        <figure class="mb-8 rounded-lg overflow-hidden">
            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}"
                 class="w-full object-cover">
        </figure>
    @endif

    <div class="prose prose-stone max-w-none prose-headings:font-bold prose-a:text-apcq prose-a:no-underline hover:prose-a:underline">
        {!! $post->content !!}
    </div>

    <div class="mt-12 pt-8 border-t border-stone-200">
        <a href="{{ route('posts.index') }}" class="inline-flex items-center font-medium text-apcq hover:underline">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux nouvelles
        </a>
    </div>

    @if($galleryImages->isNotEmpty())
        <section class="mb-8 mt-12">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-stone-500">Galerie</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach($galleryImages as $imagePath)
                    <figure class="overflow-hidden rounded-lg border border-stone-200">
                        <img
                            src="{{ asset('storage/' . $imagePath) }}"
                            alt="Image de la nouvelle {{ $post->title }}"
                            class="h-64 w-full object-cover"
                            loading="lazy"
                        >
                    </figure>
                @endforeach
            </div>
        </section>
    @endif
</article>
@endsection
