<?php

namespace App\Filament\Resources\Trainings\Schemas;

use App\Enums\TrainingStatus;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

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
                                    TextInput::make('module_number')
                                        ->label('Numéro de module')
                                        ->required()
                                        ->integer()
                                        ->minValue(1)
                                        ->maxValue(999),
                                    TextInput::make('capsule_number')
                                        ->label('Numéro de capsule')
                                        ->helperText('Les formations sont classées par module, puis par capsule.')
                                        ->required()
                                        ->integer()
                                        ->minValue(1)
                                        ->maxValue(999)
                                        ->unique(
                                            ignoreRecord: true,
                                            modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule
                                                ->where('module_number', (int) $get('module_number'))
                                                ->where(fn ($query) => $query->where('status', '!=', TrainingStatus::Archived->value)),
                                        )
                                        ->validationMessages([
                                            'unique' => 'Une autre formation non archivée utilise déjà ce numéro de capsule dans ce module.',
                                        ]),
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
                                    TextInput::make('video_duration')
                                        ->label('Durée de la vidéo (secondes)')
                                        ->helperText('Récupérée automatiquement depuis Vimeo à l’enregistrement. À saisir manuellement si elle reste vide : le suivi du visionnement en dépend.')
                                        ->integer()
                                        ->minValue(1)
                                        ->suffix('s'),
                                ])
                                ->columns(2),
                            Section::make('Test d’apprentissage')
                                ->description('Questionnaire offert sous la vidéo, dans la section « Pour aller plus loin ».')
                                ->schema([
                                    TextInput::make('learning_test_url')
                                        ->label('Lien vers le test')
                                        ->placeholder('https://')
                                        ->url()
                                        ->startsWith(['https://'])
                                        ->maxLength(2048)
                                        ->validationMessages([
                                            'starts_with' => 'Le lien doit commencer par https://.',
                                        ]),
                                ]),
                            Section::make('Documents complémentaires')
                                ->description('Fichiers offerts aux employés sous la vidéo. L’ordre se change par glisser-déposer.')
                                ->schema([
                                    Repeater::make('documents')
                                        ->hiddenLabel()
                                        ->relationship()
                                        ->orderColumn('sort_order')
                                        ->reorderableWithDragAndDrop()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                        ->addActionLabel('Ajouter un document')
                                        ->defaultItems(0)
                                        ->schema([
                                            TextInput::make('title')
                                                ->label('Titre')
                                                ->required()
                                                ->maxLength(255),
                                            Textarea::make('description')
                                                ->label('Description')
                                                ->rows(3)
                                                ->maxLength(1000),
                                            SpatieMediaLibraryFileUpload::make('file')
                                                ->label('Fichier')
                                                ->collection('file')
                                                ->disk('local')
                                                ->visibility('private')
                                                ->downloadable()
                                                ->preserveFilenames()
                                                ->maxSize(20480)
                                                ->required(),
                                        ]),
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
