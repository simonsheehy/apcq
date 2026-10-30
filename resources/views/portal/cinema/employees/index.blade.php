@use('App\Enums\EmployeeStatus')

@extends('layouts.portal')

@section('title', 'Mon cinéma')

@section('content')
    <div class="mx-auto w-full max-w-5xl px-4 pb-20 pt-4 sm:px-6">
        <p class="text-xs uppercase tracking-wide text-white/60">Administration</p>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ $cinema->name }}</h1>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('portal.cinema.report') }}" class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold ring-1 ring-white/40 transition hover:text-apcq hover:ring-apcq">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Télécharger le rapport (Excel)
                </a>
                <a href="{{ route('portal.cinema.employees.create') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-portal transition hover:bg-apcq hover:text-white">
                    Ajouter un employé
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="mt-6 rounded-xl bg-apcq/15 px-4 py-3 text-sm text-apcq" role="status">
                {{ session('status') }}
            </div>
        @endif

        <section class="mt-10" aria-labelledby="cinema-employees-heading">
            <h2 id="cinema-employees-heading" class="text-2xl font-bold tracking-tight">Employés et progression</h2>

            @if($employees->isEmpty())
                <p class="mt-6 text-white/70">Aucun employé pour le moment.</p>
            @else
                <ul class="mt-6 divide-y divide-white/10 rounded-2xl bg-white/5">
                    @foreach($employees as $employee)
                        @php
                            $progress = $progressByEmployee->get($employee->id, ['completed' => 0, 'in_progress' => 0, 'total' => 0]);
                            $percent = $progress['total'] > 0 ? (int) floor($progress['completed'] / $progress['total'] * 100) : 0;
                        @endphp
                        <li class="grid gap-4 px-5 py-5 md:grid-cols-12 md:items-center">
                            <div class="min-w-0 md:col-span-4">
                                <a href="{{ route('portal.cinema.employees.show', $employee) }}" class="font-semibold hover:text-apcq">{{ $employee->full_name }}</a>
                                @if($employee->is_cinema_admin)
                                    <span class="ml-2 rounded-full bg-white/10 px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-wide text-white/70">Admin</span>
                                @endif
                                <p class="truncate text-sm text-white/60">{{ $employee->email }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <span @class([
                                    'rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-400/15 text-emerald-300' => $employee->status === EmployeeStatus::Active,
                                    'bg-amber-400/15 text-amber-300' => $employee->status === EmployeeStatus::Invited,
                                    'bg-white/10 text-white/60' => $employee->status === EmployeeStatus::Deactivated,
                                ])>{{ $employee->status->getLabel() }}</span>
                            </div>

                            <div class="md:col-span-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full rounded-full bg-apcq" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="w-12 text-right text-sm">{{ $percent }} %</span>
                                </div>
                                <p class="mt-1 text-xs text-white/60">
                                    {{ $progress['completed'] }} / {{ $progress['total'] }} complétée(s)@if($progress['in_progress'] > 0), {{ $progress['in_progress'] }} en cours @endif
                                </p>
                            </div>

                            <div class="md:col-span-3 md:text-right">
                                <details class="relative inline-block text-left" data-actions-menu>
                                    <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-white/30 transition hover:ring-apcq [&::-webkit-details-marker]:hidden">
                                        Actions
                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                        <span class="sr-only">pour {{ $employee->full_name }}</span>
                                    </summary>

                                    <div class="absolute left-0 z-20 mt-2 w-56 rounded-xl bg-neutral-800 p-1.5 shadow-xl ring-1 ring-white/10 md:left-auto md:right-0">
                                        <a href="{{ route('portal.cinema.employees.show', $employee) }}" class="block w-full rounded-lg px-3 py-2 text-left text-sm transition hover:bg-white/10">Voir la progression</a>

                                        @if($administrator->canManage($employee))
                                            <a href="{{ route('portal.cinema.employees.edit', $employee) }}" class="block w-full rounded-lg px-3 py-2 text-left text-sm transition hover:bg-white/10">Modifier</a>

                                            @if($employee->status === EmployeeStatus::Invited)
                                                <form method="POST" action="{{ route('portal.cinema.employees.resend-invitation', $employee) }}">
                                                    @csrf
                                                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm transition hover:bg-white/10">Renvoyer l’invitation</button>
                                                </form>
                                            @endif

                                            @if($employee->isDeactivated())
                                                <form method="POST" action="{{ route('portal.cinema.employees.reactivate', $employee) }}">
                                                    @csrf
                                                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm transition hover:bg-white/10">Réactiver</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('portal.cinema.employees.deactivate', $employee) }}" onsubmit="return confirm({{ Js::from('Désactiver l’accès de '.$employee->full_name.' ? Son historique de formation sera conservé.') }})">
                                                    @csrf
                                                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm transition hover:bg-white/10">Désactiver</button>
                                                </form>
                                            @endif

                                            <div class="my-1 border-t border-white/10"></div>

                                            <form method="POST" action="{{ route('portal.cinema.employees.destroy', $employee) }}" onsubmit="return confirm({{ Js::from('Supprimer définitivement '.$employee->full_name.' ? Son historique de formation sera effacé. Cette action est irréversible.') }})">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm transition hover:bg-white/10 text-portal-coral hover:bg-portal-coral/15">Supprimer</button>
                                            </form>
                                        @endif
                                    </div>
                                </details>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <p class="mt-4 text-sm text-white/60">La progression compte les formations publiées complétées. Désactiver un employé lui retire l’accès en gardant son historique ; le supprimer efface aussi son historique.</p>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        // Closes the action menus on an outside click or Escape, and keeps a single menu open.
        document.addEventListener('click', (event) => {
            document.querySelectorAll('[data-actions-menu][open]').forEach((menu) => {
                if (!menu.contains(event.target)) {
                    menu.removeAttribute('open');
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('[data-actions-menu][open]').forEach((menu) => menu.removeAttribute('open'));
            }
        });
    </script>
@endpush
