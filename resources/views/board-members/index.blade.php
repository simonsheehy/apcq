@extends('layouts.app')

@section('title', 'Conseil d\'administration')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 py-12 sm:px-6">
    <div class="mb-10">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-apcq">Gouvernance</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Conseil d'administration</h1>
        <p class="mt-2 text-sm text-slate-600">Découvrez les membres du conseil d'administration de l'APCQ.</p>
    </div>

    @if($boardMembers->isNotEmpty())
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach($boardMembers as $boardMember)
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    @if($boardMember->photo)
                        <a href="{{ route('board-members.show', $boardMember->slug) }}">
                            <img
                                src="{{ asset('storage/' . $boardMember->photo) }}"
                                alt="{{ $boardMember->name }}"
                                class="h-64 w-full object-cover"
                            >
                        </a>
                    @else
                        <div class="flex h-64 w-full items-center justify-center bg-slate-100 text-sm font-medium text-slate-500">
                            Photo à venir
                        </div>
                    @endif
                    <div class="p-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-apcq">{{ $boardMember->title }}</p>
                        <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900">
                            <a href="{{ route('board-members.show', $boardMember->slug) }}" class="hover:text-apcq transition">
                                {{ $boardMember->name }}
                            </a>
                        </h2>
                        @if($boardMember->email || $boardMember->phone)
                            <div class="mt-4 space-y-1 text-sm">
                                @if($boardMember->email)
                                    <a href="mailto:{{ $boardMember->email }}" class="block text-apcq hover:underline">{{ $boardMember->email }}</a>
                                @endif
                                @if($boardMember->phone)
                                    <a href="tel:{{ $boardMember->phone }}" class="block text-apcq hover:underline">{{ $boardMember->phone }}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $boardMembers->links() }}
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600">
            Aucun membre du conseil pour le moment.
        </div>
    @endif
</div>
@endsection
