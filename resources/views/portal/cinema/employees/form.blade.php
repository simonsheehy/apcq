@extends('layouts.portal')

@section('title', $employee->exists ? 'Modifier '.$employee->full_name : 'Ajouter un employé')

@section('content')
    <div class="mx-auto w-full max-w-md px-4 pb-20 pt-4 sm:px-6">
        <a href="{{ route('portal.cinema.employees.index') }}" class="inline-flex items-center gap-2 text-sm text-white/70 transition hover:text-white">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Mon cinéma
        </a>

        <h1 class="mt-8 text-4xl font-bold tracking-tight">{{ $employee->exists ? 'Modifier l’employé' : 'Ajouter un employé' }}</h1>
        <p class="mt-4 text-sm text-white/70">
            @if($employee->exists)
                Si vous changez le courriel, les liens déjà envoyés ne fonctionneront plus{{ $employee->hasAcceptedInvitation() ? '' : ' et l’invitation sera renvoyée à la nouvelle adresse' }}.
            @else
                L’employé recevra par courriel un lien pour accéder directement au Centre d’expertise.
            @endif
        </p>

        <form method="POST" action="{{ $employee->exists ? route('portal.cinema.employees.update', $employee) : route('portal.cinema.employees.store') }}" class="mt-8 flex flex-col gap-5">
            @csrf
            @if($employee->exists)
                @method('PUT')
            @endif

            <x-portal.input name="first_name" label="Prénom" :value="$employee->first_name" autocomplete="off" required />
            <x-portal.input name="last_name" label="Nom" :value="$employee->last_name" autocomplete="off" required />
            <x-portal.input name="email" label="Courriel" type="email" :value="$employee->email" autocomplete="off" required />

            <x-portal.button>{{ $employee->exists ? 'Enregistrer' : 'Ajouter et envoyer l’invitation' }}</x-portal.button>
        </form>
    </div>
@endsection
