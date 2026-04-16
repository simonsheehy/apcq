@extends('layouts.app')

@section('title', $teamMember->name)

@section('content')
<article class="mx-auto w-full max-w-5xl px-4 py-12 sm:px-6">
    <div class="mb-8">
        <a href="{{ route('team-members.index') }}" class="inline-flex items-center text-sm font-semibold text-apcq hover:underline">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux membres de l'équipe
        </a>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <div>
            @if($teamMember->photo)
                <img
                    src="{{ asset('storage/' . $teamMember->photo) }}"
                    alt="{{ $teamMember->name }}"
                    class="w-full rounded-xl border border-slate-200 object-cover shadow-sm"
                >
            @else
                <div class="flex h-80 w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-sm font-medium text-slate-500">
                    Photo à venir
                </div>
            @endif
        </div>

        <div class="lg:col-span-2">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-apcq">{{ $teamMember->title }}</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $teamMember->name }}</h1>

            @if($teamMember->email || $teamMember->phone || $teamMember->linkedin_url)
                <div class="mt-6 space-y-2 text-sm">
                    @if($teamMember->email)
                        <p>
                            <span class="font-semibold text-slate-700">Courriel :</span>
                            <a href="mailto:{{ $teamMember->email }}" class="text-apcq hover:underline">{{ $teamMember->email }}</a>
                        </p>
                    @endif
                    @if($teamMember->phone)
                        <p>
                            <span class="font-semibold text-slate-700">Téléphone :</span>
                            <a href="tel:{{ $teamMember->phone }}" class="text-apcq hover:underline">{{ $teamMember->phone }}</a>
                        </p>
                    @endif
                    @if($teamMember->linkedin_url)
                        <p>
                            <span class="font-semibold text-slate-700">LinkedIn :</span>
                            <a href="{{ $teamMember->linkedin_url }}" target="_blank" rel="noopener" class="text-apcq hover:underline">
                                Voir le profil
                            </a>
                        </p>
                    @endif
                </div>
            @endif

            @if($teamMember->bio)
                <div class="prose prose-stone mt-8 max-w-none prose-a:text-apcq prose-a:no-underline hover:prose-a:underline">
                    {!! $teamMember->bio !!}
                </div>
            @endif
        </div>
    </div>
</article>
@endsection
