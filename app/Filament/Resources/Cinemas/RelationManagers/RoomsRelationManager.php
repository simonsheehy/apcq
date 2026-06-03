<?php

namespace App\Filament\Resources\Cinemas\RelationManagers;

use App\Filament\Resources\Rooms\Schemas\RoomForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomsRelationManager extends RelationManager
{
    protected static string $relationship = 'rooms';

    protected static ?string $title = 'Salles';

    public function form(Schema $schema): Schema
    {
        return $schema->components(RoomForm::components(includeCinema: false));
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Salle')
                    ->searchable(),
                TextColumn::make('roomType.name')
                    ->label('Type')
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
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
