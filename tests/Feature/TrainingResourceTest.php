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

it('lists trainings by module, then capsule', function () {
    $second = Training::factory()->create(['module_number' => 1, 'capsule_number' => 2]);
    $first = Training::factory()->draft()->create(['module_number' => 1, 'capsule_number' => 1]);

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
            'module_number' => 1,
            'capsule_number' => 1,
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
            'module_number' => 1,
            'capsule_number' => 1,
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
            'module_number' => 1,
            'capsule_number' => 1,
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
            'module_number' => 1,
            'capsule_number' => 1,
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
    $second = Training::factory()->create(['module_number' => 2, 'capsule_number' => 1]);
    $first = Training::factory()->create(['module_number' => 1, 'capsule_number' => 3]);
    Training::factory()->draft()->create(['module_number' => 1, 'capsule_number' => 1]);
    Training::factory()->archived()->create(['module_number' => 1, 'capsule_number' => 1]);

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

it('rejects a capsule number already used in the same module', function () {
    Training::factory()->create(['module_number' => 1, 'capsule_number' => 1]);

    Livewire::test(CreateTraining::class)
        ->fillForm([
            'title' => 'Doublon',
            'slug' => 'doublon',
            'module_number' => 1,
            'capsule_number' => 1,
        ])
        ->call('create')
        ->assertHasFormErrors(['capsule_number' => 'unique']);
});

it('allows reusing the numbers of an archived capsule or another module', function (int $moduleNumber, string $state) {
    Http::fake(['vimeo.com/*' => Http::response(['duration' => 600])]);
    Training::factory()->{$state}()->create(['module_number' => 1, 'capsule_number' => 1]);

    Livewire::test(CreateTraining::class)
        ->fillForm([
            'title' => 'Nouvelle capsule',
            'slug' => 'nouvelle-capsule',
            'module_number' => $moduleNumber,
            'capsule_number' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
})->with([
    'archived capsule, same module' => [1, 'archived'],
    'published capsule, other module' => [2, 'published'],
]);
