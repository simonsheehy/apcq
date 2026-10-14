@extends('portal.auth.layout')

@section('title', 'Bienvenue, '.$employee->first_name)

@section('intro', 'Choisissez votre mot de passe pour activer votre accès au Centre d’expertise de l’APCQ.')

@section('form')
    <form method="POST" action="{{ $action }}" class="flex flex-col gap-5">
        @csrf

        <x-portal.input name="email" label="Courriel" type="email" :value="$employee->email" autocomplete="username" readonly class="opacity-60" />
        <x-portal.input name="password" label="Mot de passe" type="password" autocomplete="new-password" required autofocus />
        <x-portal.input name="password_confirmation" label="Confirmer le mot de passe" type="password" autocomplete="new-password" required />

        <x-portal.button>Activer mon accès</x-portal.button>
    </form>
@endsection
