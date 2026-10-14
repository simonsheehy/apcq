<?php

namespace App\Filament\Pages\TrainingReport;

use App\Enums\TrainingProgressStatus;
use App\Enums\TrainingStatus;
use App\Exports\TrainingReportExport;
use App\Models\Cinema;
use App\Models\Training;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use UnitEnum;

class TrainingReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Formation';

    protected static ?string $navigationLabel = 'Rapport de suivi';

    protected static ?string $title = 'Rapport de suivi';

    protected static ?string $slug = 'rapport-formations';

    protected static ?int $navigationSort = 30;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CinemaCompletionSummary::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadExcel')
                ->label('Télécharger Excel')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->tooltip('Exporte le rapport filtré et le sommaire par cinéma.')
                ->action(fn (TrainingReportExport $export): BinaryFileResponse => $export->download(
                    $this->reportFilters(),
                    $this->getTableSortColumn(),
                    $this->getTableSortDirection(),
                )),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (?string $sortColumn, ?string $sortDirection, int|string $page, int|string $recordsPerPage): LengthAwarePaginator => $this->paginateRows($sortColumn, $sortDirection, $page, $recordsPerPage))
            ->columns([
                TextColumn::make('employee_name')
                    ->label('Employé')
                    ->description(fn (array $record): string => $record['employee_email'])
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cinema_name')
                    ->label('Cinéma')
                    ->sortable(),
                TextColumn::make('training_title')
                    ->label('Formation')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('progress_percent')
                    ->label('Progression')
                    ->formatStateUsing(fn (int $state): string => "{$state} %")
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Complétée le')
                    ->date('d/m/Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('downloaded_documents_count')
                    ->label('Documents téléchargés')
                    ->state(fn (array $record): string => $record['documents_count'] > 0
                        ? "{$record['downloaded_documents_count']} / {$record['documents_count']}"
                        : '—'),
                TextColumn::make('last_activity_at')
                    ->label('Dernière activité')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('cinema')
                    ->label('Cinéma')
                    ->options(fn (): array => Cinema::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                SelectFilter::make('training')
                    ->label('Formation')
                    ->options(fn (): array => Training::query()
                        ->whereIn('status', [TrainingStatus::Published, TrainingStatus::Archived])
                        ->ordered()
                        ->pluck('title', 'id')
                        ->all())
                    ->placeholder('Toutes les formations publiées'),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(TrainingProgressStatus::class),
                Filter::make('include_deactivated')
                    ->label('Inclure les employés désactivés')
                    ->toggle(),
            ])
            ->emptyStateHeading('Aucun résultat')
            ->emptyStateDescription('Aucun employé ou aucune formation publiée ne correspond aux filtres.');
    }

    /**
     * @return array{cinema_id: mixed, training_id: mixed, status: mixed, include_deactivated: bool, search: ?string}
     */
    public function reportFilters(): array
    {
        return [
            'cinema_id' => $this->tableFilters['cinema']['value'] ?? null,
            'training_id' => $this->tableFilters['training']['value'] ?? null,
            'status' => $this->tableFilters['status']['value'] ?? null,
            'include_deactivated' => (bool) ($this->tableFilters['include_deactivated']['isActive'] ?? false),
            'search' => $this->getTableSearch(),
        ];
    }

    protected function paginateRows(?string $sortColumn, ?string $sortDirection, int|string $page, int|string $recordsPerPage): LengthAwarePaginator
    {
        $query = app(TrainingReportQuery::class)->rows($this->reportFilters(), $sortColumn, $sortDirection);

        $perPage = $recordsPerPage === 'all' ? max(1, $query->getCountForPagination()) : (int) $recordsPerPage;

        $paginator = $query->paginate($perPage, page: (int) $page);

        return $paginator->setCollection(
            $paginator->getCollection()
                ->mapWithKeys(function (object $row): array {
                    $record = TrainingReportQuery::present($row);

                    return [$record['key'] => $record];
                }),
        );
    }
}
