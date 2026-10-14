<?php

use App\Enums\TrainingStatus;
use App\Filament\Resources\Trainings\Pages\CreateTraining;
use App\Filament\Resources\Trainings\Pages\EditTraining;
use App\Filament\Resources\Trainings\Pages\ListTrainings;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
