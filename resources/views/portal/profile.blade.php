@extends('layouts.portal')

@section('title', 'Mon profil')

@php
    $hasPassword = $employee->password !== null;
@endphp

@section('content')
    <div class="mx-auto w-full max-w-3xl px-4 pb-20 pt-4 sm:px-6">
        <a href="{{ route('portal.home') }}" class="inline-flex items-center gap-2 text-sm text-white/70 transition hover:text-white">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Tous les modules
        </a>

        <h1 class="mt-8 text-4xl font-bold tracking-tight sm:text-5xl">Mon profil</h1>

        @if(session('status'))
            <div class="mt-6 rounded-xl bg-apcq/15 px-4 py-3 text-sm text-apcq" role="status">
                {{ session('status') }}
            </div>
        @endif

        <section class="mt-10" aria-labelledby="profile-information-heading">
            <h2 id="profile-information-heading" class="text-2xl font-bold tracking-tight">Mes informations</h2>

            <dl class="mt-6 divide-y divide-white/10 rounded-2xl bg-white/5">
                @foreach([
                    'Prénom' => $employee->first_name,
                    'Nom' => $employee->last_name,
                    'Courriel' => $employee->email,
                    'Cinéma' => $employee->cinema?->name,
                ] as $label => $value)
                    <div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm text-white/60">{{ $label }}</dt>
                        <dd class="font-semibold sm:col-span-2">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            <p class="mt-3 text-sm text-white/60">Une information est inexacte ? Demandez à l’APCQ de la corriger.</p>
        </section>

        <section class="mt-14" aria-labelledby="profile-password-heading">
            <h2 id="profile-password-heading" class="text-2xl font-bold tracking-tight">
                {{ $hasPassword ? 'Modifier mon mot de passe' : 'Ajouter un mot de passe' }}
            </h2>
            <p class="mt-3 text-white/75">
                @if($hasPassword)
                    Vous pouvez vous connecter avec votre mot de passe ou avec un lien reçu par courriel.
                @else
                    Vous vous connectez actuellement avec un lien reçu par courriel. Ajoutez un mot de passe si vous préférez vous connecter sans attendre de courriel.
                @endif
            </p>

            <form method="POST" action="{{ route('portal.profile.password.update') }}" class="mt-6 flex max-w-md flex-col gap-5">
                @csrf
                @method('PUT')

                <input type="text" name="username" value="{{ $employee->email }}" autocomplete="username" class="hidden" readonly>

                @if($hasPassword)
                    <x-portal.input name="current_password" label="Mot de passe actuel" type="password" autocomplete="current-password" required />
                @endif

                <x-portal.input name="password" label="{{ $hasPassword ? 'Nouveau mot de passe' : 'Mot de passe' }}" type="password" autocomplete="new-password" required />
                <x-portal.input name="password_confirmation" label="Confirmer le mot de passe" type="password" autocomplete="new-password" required />

                <p class="-mt-2 text-xs text-white/50">Au moins 8 caractères.</p>

                <x-portal.button>{{ $hasPassword ? 'Modifier le mot de passe' : 'Ajouter le mot de passe' }}</x-portal.button>
            </form>
        </section>
    </div>
@endsection
