@extends('layouts.portal')

@section('title', $training->title)

@php
    $progressPercent = $progress?->progress_percent ?? 0;
    $isCompleted = $progress?->isCompleted() ?? false;
@endphp

@section('content')
    <article class="mx-auto w-full max-w-5xl px-4 pb-20 pt-4 sm:px-6">
        <a href="{{ route('portal.home') }}#modules" class="inline-flex items-center gap-2 text-sm text-white/70 transition hover:text-white">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Tous les modules
        </a>

        <p class="mt-8 text-xs uppercase tracking-wide underline underline-offset-2">Module {{ $moduleNumber }}</p>
        <h1 class="mt-2 text-4xl font-bold tracking-tight sm:text-5xl">{{ $training->title }}</h1>

        @if($training->vimeoEmbedUrl())
            <div
                class="mt-10 aspect-video overflow-hidden rounded-2xl bg-black shadow-2xl"
                data-training-player
                data-progress-url="{{ route('portal.trainings.progress', ['publishedTraining' => $training->slug]) }}"
                data-resume-position="{{ $progress?->last_position ?? 0 }}"
            >
                <iframe
                    src="{{ $training->vimeoEmbedUrl() }}"
                    class="size-full"
                    title="{{ $training->title }}"
                    allow="autoplay; fullscreen; picture-in-picture"
                ></iframe>
            </div>

            <div class="mt-4 flex items-center gap-4">
                <div class="h-2 flex-1 overflow-hidden rounded-full bg-white/10">
                    <div class="h-full rounded-full bg-apcq transition-all duration-500" style="width: {{ $progressPercent }}%" data-progress-bar></div>
                </div>
                <p class="w-40 text-right text-sm text-white/80" data-progress-label>
                    {{ $isCompleted ? 'Formation complétée' : $progressPercent.' % visionné' }}
                </p>
            </div>
        @else
            <p class="mt-10 rounded-2xl bg-white/5 px-6 py-10 text-center text-white/70">La vidéo de cette formation sera bientôt disponible.</p>
        @endif

        @if(filled($training->description))
            <div class="prose prose-invert mt-12 max-w-none">
                {!! str($training->description)->sanitizeHtml() !!}
            </div>
        @endif

        @if($documents->isNotEmpty())
            <section class="mt-14">
                <h2 class="text-2xl font-bold tracking-tight">Documents complémentaires</h2>

                <ul class="mt-6 divide-y divide-white/10 rounded-2xl bg-white/5">
                    @foreach($documents as $document)
                        @php($file = $document->file())
                        @continue($file === null)
                        <li class="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold">{{ $document->title }}</p>
                                @if(filled($document->description))
                                    <p class="mt-1 text-sm leading-relaxed text-white/75">{!! nl2br(e($document->description)) !!}</p>
                                @endif
                                <p class="mt-2 text-xs uppercase text-white/50">{{ $file->extension }} · {{ $file->human_readable_size }}</p>
                            </div>

                            <div class="flex items-center gap-4">
                                @if($downloadedDocumentIds->contains($document->id))
                                    <span class="text-xs text-apcq">✓ Téléchargé</span>
                                @endif
                                <a href="{{ route('portal.trainings.documents.show', ['publishedTraining' => $training->slug, 'document' => $document]) }}" class="rounded-full bg-white px-4 py-1.5 text-sm font-semibold text-portal transition hover:bg-apcq hover:text-white">
                                    Télécharger
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </article>
@endsection

@if($training->vimeoEmbedUrl())
    @push('scripts')
        <script src="https://player.vimeo.com/api/player.js"></script>
        @vite('resources/js/training-player.js')
    @endpush
@endif
