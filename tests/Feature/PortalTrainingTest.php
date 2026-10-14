<?php

use App\Models\Employee;
use App\Models\Training;
use App\Models\TrainingDocumentDownload;
use App\Models\TrainingProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->employee = Employee::factory()->create();
    $this->actingAs($this->employee, 'employee');
});

function progressUrl(Training $training): string
{
    return route('portal.trainings.progress', ['publishedTraining' => $training->slug]);
}

describe('training page', function () {
    it('shows a published training with its vimeo player', function () {
        $training = Training::factory()->create(['vimeo_video_id' => '76979871']);

        $this->get($training->portalUrl())
            ->assertOk()
            ->assertSee($training->title)
            ->assertSee('https://player.vimeo.com/video/76979871', escape: false);
    });

    it('hides draft and archived trainings', function (string $state) {
        $training = Training::factory()->{$state}()->create();

        $this->get($training->portalUrl())->assertNotFound();
    })->with(['draft', 'archived']);

    it('requires a logged in employee', function () {
        auth('employee')->logout();
        $training = Training::factory()->create();

        $this->get($training->portalUrl())->assertRedirect(route('portal.login'));
    });

    it('resumes where the employee stopped', function () {
        $training = Training::factory()->create();
        TrainingProgress::factory()->for($this->employee)->for($training)->create(['last_position' => 245]);

        $this->get($training->portalUrl())
            ->assertSee('data-resume-position="245"', escape: false)
            ->assertSee('20 % visionné');
    });

    it('links the home cards to the training page with the employee progress', function () {
        $training = Training::factory()->create();
        TrainingProgress::factory()->for($this->employee)->for($training)->completed()->create();

        $this->get(route('portal.home'))
            ->assertSee($training->portalUrl(), escape: false)
            ->assertSee('✓ Complétée');
    });
});

describe('progress reporting', function () {
    it('records the watched segments', function () {
        $training = Training::factory()->create();

        $this->postJson(progressUrl($training), [
            'segments' => [[0, 10.4]],
            'duration' => 100,
            'position' => 10.4,
        ])->assertOk()->assertJson(['progress_percent' => 10, 'completed' => false]);

        expect($this->employee->trainingProgress()->sole())
            ->watched_seconds->toBe(10)
            ->last_position->toBe(10)
            ->video_duration->toBe(100);
    });

    it('does not accept progress for an unpublished training', function () {
        $training = Training::factory()->draft()->create();

        $this->postJson(progressUrl($training), [
            'segments' => [[0, 10]],
            'duration' => 100,
            'position' => 10,
        ])->assertNotFound();
    });

    it('validates the payload', function () {
        $training = Training::factory()->create();

        $this->postJson(progressUrl($training), [
            'segments' => [[0]],
            'duration' => 0,
        ])->assertUnprocessable()->assertJsonValidationErrors(['segments.0', 'duration', 'position']);
    });

    it('keeps the progress of each employee separate', function () {
        $training = Training::factory()->create();
        TrainingProgress::factory()->for(Employee::factory())->for($training)->completed()->create();

        $this->postJson(progressUrl($training), [
            'segments' => [[0, 10]],
            'duration' => 600,
            'position' => 10,
        ])->assertJson(['completed' => false]);
    });
});

describe('documents', function () {
    beforeEach(function () {
        Storage::fake('local');
    });

    it('downloads a document and records it for the employee', function () {
        $training = Training::factory()->create();
        $document = $training->addMedia(UploadedFile::fake()->create('guide.pdf', 10, 'application/pdf'))
            ->toMediaCollection('documents');
        $url = route('portal.trainings.documents.show', ['publishedTraining' => $training->slug, 'document' => $document]);

        $this->get($url)->assertOk()->assertDownload('guide.pdf');
        $this->get($url)->assertOk();

        expect(TrainingDocumentDownload::sole())
            ->employee_id->toBe($this->employee->id)
            ->training_id->toBe($training->id)
            ->download_count->toBe(2);

        $this->get($training->portalUrl())->assertSee('✓ Téléchargé');
    });

    it('refuses a file that is not a document of this training', function () {
        $training = Training::factory()->create();
        $otherTraining = Training::factory()->create();
        $foreignDocument = $otherTraining->addMedia(UploadedFile::fake()->create('autre.pdf', 10))
            ->toMediaCollection('documents');

        $this->get(route('portal.trainings.documents.show', ['publishedTraining' => $training->slug, 'document' => $foreignDocument]))
            ->assertNotFound();

        expect(TrainingDocumentDownload::count())->toBe(0);
    });
});
