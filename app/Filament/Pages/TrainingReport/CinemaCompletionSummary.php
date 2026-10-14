<?php

namespace App\Filament\Pages\TrainingReport;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class CinemaCompletionSummary extends TableWidget
{
    protected static ?string $heading = 'Taux de complétion par cinéma';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Taux de complétion par cinéma')
            ->description('Formations publiées, employés actifs.')
            ->records(fn (): array => app(TrainingReportQuery::class)
                ->summaryByCinema()
                ->keyBy('cinema_name')
                ->all())
            ->columns([
                TextColumn::make('cinema_name')
                    ->label('Cinéma'),
                TextColumn::make('employees_count')
                    ->label('Employés')
                    ->numeric(),
                TextColumn::make('completed_count')
                    ->label('Formations complétées')
                    ->state(fn (array $record): string => "{$record['completed_count']} / {$record['assignments_count']}"),
                TextColumn::make('in_progress_count')
                    ->label('En cours')
                    ->numeric(),
                TextColumn::make('completion_rate')
                    ->label('Taux de complétion')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => "{$state} %")
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 40 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading('Aucun employé actif');
    }
}
