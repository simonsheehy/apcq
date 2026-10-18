<?php

use App\Enums\TrainingStatus;
use App\Filament\Resources\Trainings\Pages\CreateTraining;
use App\Filament\Resources\Trainings\Pages\EditTraining;
use App\Filament\Resources\Trainings\Pages\ListTrainings;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('lists trainings ordered by sort order', function () {
    $second = Training::factory()->create(['sort_order' => 2]);
    $first = Training::factory()->draft()->create(['sort_order' => 1]);

    Livewire::test(ListTrainings::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$first, $second], inOrder: true);
});

it('filters trainings by status', function () {
    $published = Training::factory()->create();
    $archived = Training::factory()->archived()->create();

    Livewire::test(ListTrainings::class)
        ->filterTable('status', TrainingStatus::Archived->value)
        ->assertCanSeeTableRecords([$archived])
        ->assertCanNotSeeTableRecords([$published]);
});

it('creates a training as draft by default', function () {
    Http::fake(['vimeo.com/*' => Http::response(['duration' => 600])]);

    Livewire::test(CreateTraining::class)
        ->fillForm([
            'title' => 'Gestion des salles',
            'slug' => 'gestion-des-salles',
            'vimeo_video_id' => '123456789',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Training::sole())
        ->title->toBe('Gestion des salles')
        ->status->toBe(TrainingStatus::Draft);
});

it('extracts the vimeo id from a pasted url', function (string $input) {
    Http::fake(['vimeo.com/*' => Http::response(['duration' => 600])]);

    Livewire::test(CreateTraining::class)
        ->fillForm([
            'title' => 'Qualité audio',
            'slug' => 'qualite-audio',
            'vimeo_video_id' => $input,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Training::sole()->vimeo_video_id)->toBe('76979871');
})->with([
    'bare id' => '76979871',
    'public url' => 'https://vimeo.com/76979871',
    'unlisted url' => 'https://vimeo.com/76979871/8272103f6e',
    'player url' => 'https://player.vimeo.com/video/76979871',
]);

it('rejects an invalid vimeo value', function () {
    Livewire::test(CreateTraining::class)
        ->fillForm([
            'title' => 'Propreté',
            'slug' => 'proprete',
            'vimeo_video_id' => 'https://youtube.com/watch?v=abc',
        ])
        ->call('create')
        ->assertHasFormErrors(['vimeo_video_id' => 'regex']);
});

it('requires a unique slug', function () {
    Training::factory()->create(['slug' => 'proprete']);

    Livewire::test(CreateTraining::class)
        ->fillForm([
            'title' => 'Propreté',
            'slug' => 'proprete',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

it('publishes a training', function () {
    $training = Training::factory()->draft()->create();

    Livewire::test(EditTraining::class, ['record' => $training->getRouteKey()])
        ->fillForm(['status' => TrainingStatus::Published])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($training->refresh()->status)->toBe(TrainingStatus::Published);
});

it('scopes published trainings in order', function () {
    $second = Training::factory()->create(['sort_order' => 2]);
    $first = Training::factory()->create(['sort_order' => 1]);
    Training::factory()->draft()->create(['sort_order' => 0]);
    Training::factory()->archived()->create(['sort_order' => 0]);

    expect(Training::published()->ordered()->pluck('id')->all())
        ->toBe([$first->id, $second->id]);
});

it('reads the video duration from vimeo when the video changes', function () {
    Http::fake([
        'vimeo.com/api/oembed.json*' => Http::response(['duration' => 312]),
    ]);

    $training = Training::factory()->create(['vimeo_video_id' => null, 'video_duration' => null]);
    $training->update(['vimeo_video_id' => '76979871']);

    expect($training->refresh()->video_duration)->toBe(312);
    Http::assertSent(fn ($request): bool => str_contains($request->url(), urlencode('https://vimeo.com/76979871')));
});

it('keeps a manually entered duration', function () {
    $training = Training::factory()->create(['vimeo_video_id' => '76979871', 'video_duration' => 90]);

    expect($training->video_duration)->toBe(90);
    Http::assertNothingSent();
});

it('leaves the duration empty when vimeo does not answer', function () {
    Http::fake([
        'vimeo.com/*' => Http::response(status: 404),
    ]);

    $training = Training::factory()->create(['vimeo_video_id' => null, 'video_duration' => null]);
    $training->update(['vimeo_video_id' => '123', 'video_duration' => null]);

    expect($training->refresh()->video_duration)->toBeNull();
});

it('adds documents with a title, a description and a file', function () {
    Storage::fake('local');
    $training = Training::factory()->create();

    Livewire::test(EditTraining::class, ['record' => $training->getRouteKey()])
        ->fillForm([
            'documents' => [
                [
                    'title' => 'Guide de vérification audio',
                    'description' => 'À consulter avant chaque projection.',
                    'file' => [UploadedFile::fake()->create('guide.pdf', 10, 'application/pdf')],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $document = $training->documents()->sole();

    expect($document)
        ->title->toBe('Guide de vérification audio')
        ->description->toBe('À consulter avant chaque projection.')
        ->and($document->file()?->file_name)->toBe('guide.pdf')
        ->and($document->file()?->disk)->toBe('local');
});

it('requires a title and a file for each document', function () {
    $training = Training::factory()->create();

    Livewire::test(EditTraining::class, ['record' => $training->getRouteKey()])
        ->fillForm(['documents' => [['title' => '', 'description' => 'Sans fichier']]])
        ->call('save')
        ->assertHasFormErrors(['documents.0.title' => 'required']);

    expect($training->documents()->count())->toBe(0);
});

it('reorders trainings, which renumbers the modules in the portal', function () {
    $audio = Training::factory()->create(['title' => 'Qualité audio', 'sort_order' => 1]);
    $cleaning = Training::factory()->create(['title' => 'Propreté', 'sort_order' => 2]);
    $sales = Training::factory()->create(['title' => 'Ventes additionnelles', 'sort_order' => 3]);

    Livewire::test(ListTrainings::class)
        ->call('toggleTableReordering')
        ->assertSet('isTableReordering', true)
        ->call('reorderTable', [(string) $sales->id, (string) $audio->id, (string) $cleaning->id])
        ->assertCanSeeTableRecords([$sales, $audio, $cleaning], inOrder: true);

    expect(Training::ordered()->pluck('title')->all())
        ->toBe(['Ventes additionnelles', 'Qualité audio', 'Propreté']);

    $this->get(route('portal.home'))
        ->assertSeeInOrder(['Module 1', 'Ventes additionnelles', 'Module 2', 'Qualité audio', 'Module 3', 'Propreté']);
});
