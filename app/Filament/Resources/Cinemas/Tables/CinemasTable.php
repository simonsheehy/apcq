<?php

namespace App\Filament\Resources\Cinemas\Tables;

use App\Filament\Actions\DownloadCinemasExcelBulkAction;
use App\Filament\Actions\SendCinemaValidationEmailBulkAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CinemasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group.name')
                    ->label('Groupe')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('administrativeRegion.name')
                    ->label('Région')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('city')
                    ->label('Ville')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rooms_count')
                    ->label('Salles')
                    ->counts('rooms')
                    ->sortable(),
                IconColumn::make('personal_info_validated_at')
                    ->label('Contact')
                    ->boolean()
                    ->tooltip(fn ($state) => $state?->timezone('America/Toronto')->translatedFormat('d F Y')),
                IconColumn::make('cinema_info_validated_at')
                    ->label('Cinéma')
                    ->boolean()
                    ->tooltip(fn ($state) => $state?->timezone('America/Toronto')->translatedFormat('d F Y')),
                IconColumn::make('alcohol_permit')
                    ->label('Alcool')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('information_validated')
                    ->label('Validation')
                    ->options([
                        'yes' => 'Validé',
                        'no' => 'Non validé',
                    ])
                    ->placeholder('Tous')
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'yes' => $query->informationValidated(),
                            'no' => $query->informationNotValidated(),
                            default => $query,
                        };
                    }),
                SelectFilter::make('group')
                    ->label('Groupe')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('administrativeRegion')
                    ->label('Région')
                    ->relationship('administrativeRegion', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('openValidationLink')
                    ->label('')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn ($record): string => $record->validationUrl())
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DownloadCinemasExcelBulkAction::make(),
                    SendCinemaValidationEmailBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
