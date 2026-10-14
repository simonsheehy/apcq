@extends('portal.auth.layout')

@section('title', 'Connexion')

@section('intro', 'Accédez au Centre d’expertise de l’APCQ avec l’adresse courriel à laquelle vous avez reçu votre invitation.')

@section('form')
    <form method="POST" action="{{ route('portal.login.store') }}" class="flex flex-col gap-5">
        @csrf

        <x-portal.input name="email" label="Courriel" type="email" autocomplete="email" required autofocus />
        <x-portal.input name="password" label="Mot de passe" type="password" autocomplete="current-password" required />

        <div class="flex items-center justify-between gap-4 text-sm">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="remember" value="1" class="size-4 rounded border-white/30 bg-white/5 accent-apcq">
                Se souvenir de moi
            </label>
            <a href="{{ route('portal.password.request') }}" class="text-white/70 underline-offset-4 hover:text-white hover:underline">
                Mot de passe oublié ?
            </a>
        </div>

        <x-portal.button>Se connecter</x-portal.button>
    </form>
@endsection
