<?php

namespace App\Filament\Resources\MenuItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('label')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('link')
                    ->label('Lien (route ou URL)')
                    ->searchable(),
                \Filament\Tables\Columns\IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),
                \Filament\Tables\Columns\TextColumn::make('location')
                    ->label('Emplacement')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'header' => 'En-tête',
                        'footer' => 'Pied de page',
                        default => $state,
                    }),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->filters([
                SelectFilter::make('location')
                    ->label('Emplacement')
                    ->options([
                        'header' => 'En-tête',
                        'footer' => 'Pied de page',
                    ]),
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
