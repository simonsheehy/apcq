<?php

namespace App\Filament\Resources\Trainings\Tables;

use App\Enums\TrainingStatus;
use App\Models\Training;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')
                    ->label('Image')
                    ->collection('cover')
                    ->conversion('card')
                    ->square(),
                ColorColumn::make('card_color')
                    ->label('Couleur')
                    ->state(fn (Training $record): ?string => $record->card_color?->hex()),
                TextColumn::make('module_number')
                    ->label('Module')
                    ->sortable(),
                TextColumn::make('capsule_number')
                    ->label('Capsule')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                IconColumn::make('vimeo_video_id')
                    ->label('Vidéo')
                    ->boolean()
                    ->state(fn ($record): bool => filled($record->vimeo_video_id)),
                TextColumn::make('documents_count')
                    ->label('Documents')
                    ->counts('documents'),
                TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('module_number')->orderBy('capsule_number'))
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(TrainingStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
