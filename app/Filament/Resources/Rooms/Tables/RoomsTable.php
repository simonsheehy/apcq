<?php

namespace App\Filament\Resources\Rooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cinema.name')
                    ->label('Cinéma')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Salle')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roomType.name')
                    ->label('Type')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('seat_allocations_sum_quantity')
                    ->label('Places')
                    ->sum('seatAllocations', 'quantity')
                    ->placeholder('0'),
                TextColumn::make('imageTechnologies.name')
                    ->label('Image')
                    ->badge()
                    ->separator(','),
                TextColumn::make('soundTechnologies.name')
                    ->label('Son')
                    ->badge()
                    ->separator(','),
                TextColumn::make('screen_size')
                    ->label('Écran')
                    ->toggleable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('cinema')
                    ->label('Cinéma')
                    ->relationship('cinema', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('roomType')
                    ->label('Type de salle')
                    ->relationship('roomType', 'name')
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
