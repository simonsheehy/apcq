@extends('layouts.portal')

@section('title', 'Centre d’expertise')

@section('content')
    <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 md:py-16">
        <h1 class="font-display text-5xl leading-none sm:text-6xl md:text-7xl">Centre<br>d’expertise</h1>
        <p class="mt-6 text-white/80">Bonjour {{ auth('employee')->user()->first_name }}, pour vous aider à exceller.</p>
    </section>

    <section class="mx-auto w-full max-w-7xl px-4 pb-16 sm:px-6">
        <h2 class="font-display text-3xl sm:text-4xl">Aperçu du centre de formation</h2>

        @if($trainings->isEmpty())
            <p class="mt-8 text-white/70">Aucune formation n’est disponible pour le moment.</p>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($trainings as $training)
                    <article>
                        <div class="aspect-video overflow-hidden rounded-xl bg-apcq/80">
                            @if($training->hasMedia('cover'))
                                <img src="{{ $training->getFirstMediaUrl('cover', 'card') }}" alt="" class="size-full object-cover">
                            @endif
                        </div>
                        <p class="mt-3 text-xs uppercase tracking-wide underline underline-offset-2">Module {{ $loop->iteration }}</p>
                        <h3 class="mt-1 text-sm font-semibold">{{ $training->title }}</h3>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
