<?php

namespace App\Filament\Pages\Settings;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;

class ParametresDuSite extends Page
{
    use CanUseDatabaseTransactions;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Paramètres du site';

    protected static ?string $title = 'Paramètres du site';

    protected static ?string $slug = 'parametres';

    protected static ?int $navigationSort = 100;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $settings = SiteSetting::instance();
        $data = $settings->attributesToArray();

        $this->callHook('beforeFill');
        $this->form->fill($data);
        $this->callHook('afterFill');
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->operation('edit')
            ->model(SiteSetting::instance())
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pied de page')
                    ->description('Informations affichées dans le pied de page')
                    ->schema([
                        Textarea::make('footer_about')
                            ->label('Texte à propos')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('footer_address')
                            ->label('Adresse')
                            ->maxLength(255),
                        TextInput::make('footer_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('footer_email')
                            ->label('Courriel')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');
            $data = $this->form->getState();
            $this->callHook('afterValidate');

            SiteSetting::instance()->update($data);
            SiteSetting::flushInstance();

            $this->callHook('afterSave');
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } finally {
            $this->commitDatabaseTransaction();
        }

        Notification::make()
            ->success()
            ->title('Paramètres enregistrés')
            ->send();
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment(\Filament\Support\Enums\Alignment::Start)
                    ->fullWidth(false)
                    ->sticky(false)
                    ->key('form-actions'),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

}
