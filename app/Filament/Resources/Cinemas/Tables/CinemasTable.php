<?php

namespace App\Filament\Resources\Cinemas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

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
                IconColumn::make('alcohol_permit')
                    ->label('Alcool')
                    ->boolean(),
            ])
            ->filters([
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
