<?php

namespace App\Filament\Resources\Groups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Groupe')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nom'),
                        TextEntry::make('main_contact')
                            ->label('Contact principal')
                            ->placeholder('—'),
                        TextEntry::make('cinemas_count')
                            ->label('Nombre de cinémas')
                            ->state(fn ($record) => $record->cinemas()->count()),
                    ])
                    ->columns(2),
            ]);
    }
}
