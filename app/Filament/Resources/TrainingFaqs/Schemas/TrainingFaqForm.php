<?php

namespace App\Filament\Resources\TrainingFaqs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrainingFaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Question')
                    ->description('Affichée dans la section FAQ de l’accueil du Centre d’expertise. L’ordre se change par glisser-déposer dans la liste.')
                    ->schema([
                        TextInput::make('question')
                            ->label('Question')
                            ->required()
                            ->maxLength(150),
                        Textarea::make('answer')
                            ->label('Réponse')
                            ->required()
                            ->rows(5)
                            ->maxLength(600)
                            ->helperText('Restez bref : la réponse s’affiche sur une carte.'),
                        Toggle::make('is_published')
                            ->label('Publiée')
                            ->default(true),
                    ]),
            ]);
    }
}
