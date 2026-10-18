@extends('layouts.portal')

@section('title', 'Centre d’expertise')

@php
    $moduleColors = ['bg-portal-cyan', 'bg-portal-yellow', 'bg-portal-mist', 'bg-portal-mist', 'bg-portal-cyan', 'bg-portal-yellow'];

    $trainingApproaches = [
        [
            'title' => 'Matériel à votre rythme',
            'text' => 'Chaque module se compose d’une vidéo et de documents complémentaires. Vous avancez quand vous le voulez, sur ordinateur, tablette ou téléphone.',
        ],
        [
            'title' => 'Ateliers de groupe programmés et mentorat individuel',
            'text' => 'Les modules servent de base commune à toute l’équipe. Votre gestionnaire peut les reprendre en groupe et vous accompagner au besoin.',
        ],
        [
            'title' => 'Application sur le terrain',
            'text' => 'Chaque formation est pensée pour la réalité d’un cinéma : vous pouvez mettre en pratique ce que vous apprenez dès votre prochain quart de travail.',
        ],
    ];

    $resources = [
        [
            'title' => 'Manuel des essentiels',
            'text' => 'Les bases à connaître pour bien accueillir le public et assurer le bon déroulement des projections.',
            'color' => 'bg-portal-mist',
            'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
        ],
        [
            'title' => 'Journal d’entraînement',
            'text' => 'Suivez les formations que vous avez commencées et terminées, et reprenez là où vous vous êtes arrêté.',
            'color' => 'bg-portal-mist',
            'icon' => 'M16.862 4.487 18.549 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10',
        ],
        [
            'title' => 'Modules',
            'text' => 'Tous les modules de formation offerts aux employés des cinémas membres de l’APCQ.',
            'color' => 'bg-portal-coral',
            'icon' => 'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0 1 18 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0 1 18 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 0 1 6 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M18 18.375v-5.25m0 5.25v-1.5c0-.621.504-1.125 1.125-1.125M18 13.125v1.5c0 .621.504 1.125 1.125 1.125M18 13.125c0-.621.504-1.125 1.125-1.125M6 13.125v1.5c0 .621-.504 1.125-1.125 1.125M6 13.125C6 12.504 5.496 12 4.875 12m-1.5 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M19.125 12h1.5m0 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h1.5m14.25 0h1.5',
        ],
    ];
@endphp

@section('content')
    {{-- Hero --}}
    <section class="mx-auto grid w-full max-w-7xl items-center gap-10 px-4 pb-16 pt-4 sm:px-6 md:grid-cols-2 md:pb-24">
        <div>
            <h1 class="font-bold tracking-tight text-6xl leading-[0.95] sm:text-7xl lg:text-8xl">Centre d’expertise</h1>
            <p class="mt-6 text-white/80">
                @auth('employee')
                    Bonjour {{ auth('employee')->user()->first_name }}, pour vous aider à exceller.
                @else
                    Pour vous aider à exceller.
                @endauth
            </p>
            <a href="#modules" class="mt-10 inline-flex min-w-36 items-center justify-center rounded-full bg-white px-8 py-2.5 text-sm font-semibold text-portal transition hover:bg-apcq hover:text-white">
                Débuter
            </a>
        </div>

        <div class="relative mx-auto aspect-[4/3] w-full max-w-xl">
            <div class="absolute inset-y-0 left-[8%] right-0 -rotate-6 rounded-[2.5rem] bg-white/90"></div>
            <img src="{{ asset('images/formations.png') }}" alt="" class="absolute inset-0 size-full rounded-[2.5rem] object-cover shadow-2xl">
        </div>
    </section>

    {{-- Modules --}}
    <section id="modules" class="mx-auto w-full max-w-7xl scroll-mt-8 px-4 pb-20 sm:px-6 md:pb-28">
        <h2 class="font-bold tracking-tight text-3xl sm:text-4xl">Aperçu du centre de formation</h2>

        @if($trainings->isEmpty())
            <p class="mt-8 text-white/70">Aucune formation n’est disponible pour le moment.</p>
        @else
            <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-8 sm:gap-x-6 lg:grid-cols-3">
                @foreach($trainings as $training)
                    @php($progress = $progressByTraining->get($training->id))
                    <a href="{{ $training->portalUrl() }}" class="group block">
                        <div class="{{ $moduleColors[$loop->index % count($moduleColors)] }} relative aspect-[16/8] overflow-hidden rounded-xl">
                            @if($training->hasMedia('cover'))
                                <img src="{{ $training->getFirstMediaUrl('cover', 'card') }}" alt="" class="size-full object-cover transition duration-300 group-hover:scale-105">
                            @endif

                            @guest('employee')
                                <span class="absolute right-2 top-2 inline-flex items-center gap-1 rounded-full bg-portal px-2.5 py-1 text-[0.65rem] font-semibold text-white">
                                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                                    </svg>
                                    Connexion requise
                                </span>
                            @endguest

                            @if($progress?->isCompleted())
                                <span class="absolute right-2 top-2 rounded-full bg-portal px-2.5 py-1 text-[0.65rem] font-semibold text-white">✓ Complétée</span>
                            @elseif($progress)
                                <span class="absolute right-2 top-2 rounded-full bg-portal px-2.5 py-1 text-[0.65rem] font-semibold text-white">{{ $progress->progress_percent }} %</span>
                                <span class="absolute inset-x-0 bottom-0 h-1 bg-portal/30">
                                    <span class="block h-full bg-portal" style="width: {{ $progress->progress_percent }}%"></span>
                                </span>
                            @endif
                        </div>
                        <p class="mt-3 text-xs uppercase tracking-wide underline underline-offset-2">{{ $training->number_label }}</p>
                        <h3 class="mt-1 text-sm font-semibold group-hover:text-apcq">{{ $training->title }}</h3>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- How to train --}}
    <section class="mx-auto grid w-full max-w-7xl items-center gap-12 px-4 pb-20 sm:px-6 md:grid-cols-2 md:pb-28">
        <h2 class="font-bold tracking-tight text-5xl leading-[1.05] sm:text-6xl">
            Comment
            <span class="relative inline-block">
                <span class="absolute -inset-x-3 inset-y-1 -z-0 -rotate-2 rounded-full bg-portal-sky" aria-hidden="true"></span>
                <span class="relative">s’entraîner</span>
            </span>
            à la tâche.
        </h2>

        <ul class="flex flex-col gap-10">
            @foreach($trainingApproaches as $approach)
                <li class="flex gap-4">
                    <x-portal.arrow-badge class="mt-0.5" />
                    <div>
                        <h3 class="font-semibold text-base">{{ $approach['title'] }}</h3>
                        <p class="mt-3 text-xs leading-relaxed text-white/80">{{ $approach['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Resources
    <section class="mx-auto w-full max-w-7xl px-4 pb-20 sm:px-6 md:pb-28">
        <h2 class="font-bold tracking-tight text-4xl leading-tight sm:text-5xl">Ressources de<br>formation</h2>

        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($resources as $resource)
                <article class="flex flex-col">
                    <div class="{{ $resource['color'] }} flex aspect-[16/7] items-center justify-center rounded-lg text-portal sm:aspect-[16/10]">
                        <svg class="size-20 opacity-80" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $resource['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-lg">{{ $resource['title'] }}</h3>
                    <p class="mt-3 text-xs font-semibold leading-relaxed text-white/85">{{ $resource['text'] }}</p>
                    <a href="#modules" class="mt-5 inline-flex self-start rounded-full bg-white px-3 py-1 text-[0.65rem] uppercase tracking-wide text-portal transition hover:bg-apcq hover:text-white">
                        Lire la suite
                    </a>
                </article>
            @endforeach
        </div>
    </section> --}}

    {{-- FAQ --}}
    @if($faqs->isNotEmpty())
        <section class="mx-auto w-full max-w-7xl px-4 pb-20 sm:px-6 md:pb-28">
            <h2 class="font-bold tracking-tight text-5xl">FAQs</h2>

            <div class="mx-auto mt-10 flex max-w-5xl flex-wrap justify-center gap-6">
                @foreach($faqs as $faq)
                    <article class="flex w-full flex-col rounded-lg bg-portal-sky p-6 md:min-h-80 md:w-[calc((100%-3rem)/3)]">
                        <x-portal.arrow-badge />
                        <h3 class="mt-5 font-semibold text-lg leading-snug">{{ $faq->question }}</h3>
                        <p class="mt-auto pt-8 text-xs leading-relaxed text-white/90">{!! nl2br(e($faq->answer)) !!}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Call to action --}}
    <section class="relative overflow-hidden bg-white text-portal">
        <div class="absolute -inset-x-16 -top-10 h-40 -rotate-2 bg-portal-lime md:h-56" aria-hidden="true"></div>

        <div class="relative mx-auto grid w-full max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 md:grid-cols-2 md:py-24">
            <div class="relative mx-auto hidden aspect-[4/5] w-full max-w-sm md:block">
                <div class="absolute inset-0 -rotate-3 rounded-[2rem] bg-portal-sky"></div>
                <div class="absolute inset-0 flex items-end justify-center overflow-hidden rounded-[2rem] bg-portal">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="mb-auto mt-auto w-2/3 object-contain">
                </div>
            </div>

            <div>
                <h2 class="font-bold tracking-tight text-4xl leading-tight sm:text-5xl">Nous avons hâte de te voir grandir !</h2>

                @if(filled($contactEmail))
                    <p class="mt-10 font-semibold">Pour toute question concernant le programme, n’hésitez pas à contacter</p>
                    <a href="mailto:{{ $contactEmail }}" class="mt-4 inline-flex min-w-64 items-center justify-center rounded-full border border-portal/20 bg-white px-6 py-2 text-sm shadow-sm transition hover:bg-portal hover:text-white">
                        {{ $contactEmail }}
                    </a>
                @endif
            </div>
        </div>
    </section>
@endsection
