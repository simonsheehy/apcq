<?php

namespace App\Filament\Resources\Trainings\Schemas;

use App\Enums\TrainingStatus;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(12)
                    ->columns(['lg' => 12])
                    ->schema([
                        Group::make([
                            Section::make('Informations')
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Titre')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                                    TextInput::make('slug')
                                        ->label('URL (slug)')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true),
                                    RichEditor::make('description')
                                        ->label('Description')
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'underline',
                                            'bulletList',
                                            'orderedList',
                                            'link',
                                            'undo',
                                            'redo',
                                        ])
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                            Section::make('Vidéo')
                                ->schema([
                                    TextInput::make('vimeo_video_id')
                                        ->label('Vidéo Vimeo')
                                        ->helperText('Collez le lien Vimeo ou l’identifiant numérique de la vidéo.')
                                        ->maxLength(255)
                                        ->regex('/^(\d+|https?:\/\/(www\.|player\.)?vimeo\.com\/.*\d+.*)$/')
                                        ->dehydrateStateUsing(fn (?string $state): ?string => self::extractVimeoVideoId($state)),
                                ]),
                            Section::make('Documents complémentaires')
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('documents')
                                        ->hiddenLabel()
                                        ->collection('documents')
                                        ->disk('local')
                                        ->visibility('private')
                                        ->multiple()
                                        ->reorderable()
                                        ->downloadable()
                                        ->preserveFilenames()
                                        ->maxSize(20480),
                                ]),
                        ])
                            ->columnSpan(['lg' => 9]),
                        Group::make([
                            Section::make('Publication')
                                ->schema([
                                    Select::make('status')
                                        ->label('Statut')
                                        ->options(TrainingStatus::class)
                                        ->default(TrainingStatus::Draft)
                                        ->required(),
                                ])
                                ->columns(1),
                            Section::make('Image')
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('cover')
                                        ->label('Image de la carte')
                                        ->collection('cover')
                                        ->disk('public')
                                        ->image()
                                        ->imageEditor()
                                        ->maxSize(4096),
                                ])
                                ->columns(1),
                        ])
                            ->columnSpan(['lg' => 3]),
                    ]),
            ]);
    }

    /**
     * Accepts a Vimeo URL (vimeo.com/123, player.vimeo.com/video/123, vimeo.com/123/abc) or a bare ID.
     */
    public static function extractVimeoVideoId(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        if (ctype_digit($value)) {
            return $value;
        }

        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $value, $matches)) {
            return $matches[1];
        }

        return $value;
    }
}
