@use('App\Enums\TrainingProgressStatus')

@extends('layouts.portal')

@section('title', $employee->full_name)

@section('content')
    <div class="mx-auto w-full max-w-4xl px-4 pb-20 pt-4 sm:px-6">
        <a href="{{ route('portal.cinema.employees.index') }}" class="inline-flex items-center gap-2 text-sm text-white/70 transition hover:text-white">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Mon cinéma
        </a>

        <h1 class="mt-8 text-4xl font-bold tracking-tight sm:text-5xl">{{ $employee->full_name }}</h1>
        <p class="mt-2 text-white/70">{{ $employee->email }} · {{ $employee->status->getLabel() }}</p>

        <section class="mt-10" aria-labelledby="employee-progress-heading">
            <h2 id="employee-progress-heading" class="text-2xl font-bold tracking-tight">Progression des formations</h2>

            @if($rows->isEmpty())
                <p class="mt-6 text-white/70">Aucune formation publiée pour le moment.</p>
            @else
                <ul class="mt-6 divide-y divide-white/10 rounded-2xl bg-white/5">
                    @foreach($rows as $row)
                        <li class="grid gap-3 px-5 py-4 sm:grid-cols-12 sm:items-center">
                            <div class="sm:col-span-6">
                                <p class="text-xs uppercase tracking-wide text-white/60">Module {{ $row['module_number'] }} · Capsule {{ $row['capsule_number'] }}</p>
                                <p class="font-semibold">{{ $row['training_title'] }}</p>
                            </div>
                            <div class="sm:col-span-3">
                                @switch($row['status'])
                                    @case(TrainingProgressStatus::Completed)
                                        <span class="text-sm text-emerald-300">✓ Complétée le {{ $row['completed_at']->timezone('America/Toronto')->format('d/m/Y') }}</span>
                                        @break
                                    @case(TrainingProgressStatus::InProgress)
                                        <span class="text-sm text-amber-300">En cours · {{ $row['progress_percent'] }} %</span>
                                        @break
                                    @default
                                        <span class="text-sm text-white/60">Non commencée</span>
                                @endswitch
                            </div>
                            <div class="text-sm text-white/60 sm:col-span-3 sm:text-right">
                                @if($row['documents_count'] > 0)
                                    Documents : {{ $row['downloaded_documents_count'] }} / {{ $row['documents_count'] }}
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
