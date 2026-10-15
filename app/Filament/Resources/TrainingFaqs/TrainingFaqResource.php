<?php

namespace App\Filament\Resources\TrainingFaqs;

use App\Filament\Resources\TrainingFaqs\Pages\CreateTrainingFaq;
use App\Filament\Resources\TrainingFaqs\Pages\EditTrainingFaq;
use App\Filament\Resources\TrainingFaqs\Pages\ListTrainingFaqs;
use App\Filament\Resources\TrainingFaqs\Schemas\TrainingFaqForm;
use App\Filament\Resources\TrainingFaqs\Tables\TrainingFaqsTable;
use App\Models\TrainingFaq;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TrainingFaqResource extends Resource
{
    protected static ?string $model = TrainingFaq::class;

    protected static string|UnitEnum|null $navigationGroup = 'Formation';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $modelLabel = 'question';

    protected static ?string $pluralModelLabel = 'questions';

    protected static ?string $recordTitleAttribute = 'question';

    protected static ?string $slug = 'formation-faq';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return TrainingFaqForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingFaqsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingFaqs::route('/'),
            'create' => CreateTrainingFaq::route('/create'),
            'edit' => EditTrainingFaq::route('/{record}/edit'),
        ];
    }
}
