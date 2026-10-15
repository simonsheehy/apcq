@extends('portal.auth.layout')

@section('title', 'Bienvenue, '.$employee->first_name)

@section('intro', 'Votre accès au Centre d’expertise de l’APCQ est prêt. Pour vos prochaines visites, il suffira de demander un lien de connexion avec votre adresse courriel.')

@section('form')
    <form method="POST" action="{{ $action }}" class="flex flex-col gap-5">
        @csrf

        <p class="rounded-xl bg-white/5 px-4 py-3 text-sm text-white/80">{{ $employee->email }}</p>

        <x-portal.button>Accéder au Centre d’expertise</x-portal.button>
    </form>
@endsection
