@extends('layouts.app')

@section('title', 'Membres de l\'équipe')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 py-12 sm:px-6">
    <div class="mb-10">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-apcq">Équipe</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Membres de l'équipe</h1>
        <p class="mt-2 text-sm text-slate-600">Découvrez les membres de l'équipe de l'APCQ.</p>
    </div>

    @if($teamMembers->isNotEmpty())
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($teamMembers as $teamMember)
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    @if($teamMember->photo)
                        <a href="{{ route('team-members.show', $teamMember->slug) }}">
                            <img
                                src="{{ asset('storage/' . $teamMember->photo) }}"
                                alt="{{ $teamMember->name }}"
                                class="h-64 w-full object-cover"
                            >
                        </a>
                    @else
                        <div class="flex h-64 w-full items-center justify-center bg-slate-100 text-sm font-medium text-slate-500">
                            Photo à venir
                        </div>
                    @endif
                    <div class="p-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-apcq">{{ $teamMember->title }}</p>
                        <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900">
                            <a href="{{ route('team-members.show', $teamMember->slug) }}" class="hover:text-apcq transition">
                                {{ $teamMember->name }}
                            </a>
                        </h2>
                        @if($teamMember->email || $teamMember->phone)
                            <div class="mt-4 space-y-1 text-sm">
                                @if($teamMember->email)
                                    <a href="mailto:{{ $teamMember->email }}" class="block text-apcq hover:underline">{{ $teamMember->email }}</a>
                                @endif
                                @if($teamMember->phone)
                                    <a href="tel:{{ $teamMember->phone }}" class="block text-apcq hover:underline">{{ $teamMember->phone }}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $teamMembers->links() }}
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600">
            Aucun membre de l'équipe pour le moment.
        </div>
    @endif
</div>
@endsection
