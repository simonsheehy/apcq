@extends('portal.auth.layout')

@section('title', 'Nouveau mot de passe')

@section('form')
    <form method="POST" action="{{ route('portal.password.update') }}" class="flex flex-col gap-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-portal.input name="email" label="Courriel" type="email" :value="$email" autocomplete="email" required />
        <x-portal.input name="password" label="Nouveau mot de passe" type="password" autocomplete="new-password" required autofocus />
        <x-portal.input name="password_confirmation" label="Confirmer le mot de passe" type="password" autocomplete="new-password" required />

        <x-portal.button>Enregistrer le mot de passe</x-portal.button>
    </form>
@endsection
