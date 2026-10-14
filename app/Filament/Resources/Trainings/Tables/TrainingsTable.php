<?php

namespace App\Filament\Resources\Trainings\Tables;

use App\Enums\TrainingStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                    ->counts([
                        'media as documents_count' => fn ($query) => $query->where('collection_name', 'documents'),
                    ]),
                TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
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
