<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(12)
                    ->columns(['lg' => 12])
                    ->schema([
                        Section::make('Élément de menu')
                            ->columnSpan(['lg' => 9])
                            ->schema([
                                TextInput::make('label')
                                    ->label('Libellé')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('link')
                                    ->label('Lien')
                                    ->helperText('Route (posts.index, about), page (page.show:slug), URL (https://...), chemin (/page/...) ou ancre (#calendrier)')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('posts.index'),
                            ])
                            ->columns(2),
                        Section::make('Options')
                            ->columnSpan(['lg' => 3])
                            ->schema([
                                Select::make('location')
                                    ->label('Emplacement')
                                    ->options([
                                        'header' => 'En-tête',
                                        'footer' => 'Pied de page',
                                    ])
                                    ->required()
                                    ->default('header'),
                                TextInput::make('sort_order')
                                    ->label('Ordre')
                                    ->numeric()
                                    ->default(0),
                                Toggle::make('is_visible')
                                    ->label('Visible')
                                    ->default(true),
                                Toggle::make('open_in_new_tab')
                                    ->label('Nouvel onglet')
                                    ->default(false),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }
}
