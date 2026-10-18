<?php

use App\Models\Employee;
use App\Models\Training;
use App\Models\TrainingDocument;
use App\Models\TrainingDocumentDownload;
use App\Models\TrainingProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
            ->assertSee('https://player.vimeo.com/video/76979871?', escape: false)
            ->assertSee('speed=0', escape: false)
            ->assertSee('title=0', escape: false);
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
            ->assertSee('data-watched-segments="[[0,120]]"', escape: false)
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
        $training = Training::factory()->create(['video_duration' => 100]);

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

    it('ignores the duration reported by the player when the training has one', function () {
        $training = Training::factory()->create(['video_duration' => 600]);

        $this->postJson(progressUrl($training), [
            'segments' => [[0, 1]],
            'duration' => 1,
            'position' => 1,
        ])->assertOk()->assertJson(['progress_percent' => 0, 'completed' => false]);
    });

    it('locks the first duration reported when the training has none', function () {
        Http::fake(['vimeo.com/*' => Http::response(status: 404)]);
        $training = Training::factory()->create(['video_duration' => null]);

        $this->postJson(progressUrl($training), ['segments' => [[0, 10]], 'duration' => 100, 'position' => 10])
            ->assertJson(['progress_percent' => 10]);

        $this->travel(1)->minutes();

        $this->postJson(progressUrl($training), ['segments' => [[0, 10]], 'duration' => 10, 'position' => 10])
            ->assertJson(['progress_percent' => 10, 'completed' => false]);
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
            'segments' => [[0], ['a' => 1, 'b' => 2]],
            'duration' => 0,
        ])->assertUnprocessable()->assertJsonValidationErrors(['segments.0', 'segments.1', 'duration', 'position']);
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

describe('practical tools', function () {
    beforeEach(function () {
        Storage::fake('local');
    });

    it('offers the learning test under the video', function () {
        $training = Training::factory()->create(['learning_test_url' => 'https://forms.example.com/test-capsule-1']);

        $this->get($training->portalUrl())
            ->assertSeeInOrder(['player.vimeo.com', 'Pour aller plus loin – Outils pratiques', 'Test d\'apprentissage', 'Faire le test'], escape: false)
            ->assertSee('href="https://forms.example.com/test-capsule-1" target="_blank" rel="noopener noreferrer"', escape: false)
            ->assertDontSee('Documents complémentaires');
    });

    it('shows the section with documents only', function () {
        $training = Training::factory()->create(['learning_test_url' => null]);
        TrainingDocument::factory()->for($training)->withFile()->create(['title' => 'Guide complet']);

        $this->get($training->portalUrl())
            ->assertSeeInOrder(['Pour aller plus loin – Outils pratiques', 'Documents complémentaires', 'Guide complet'])
            ->assertDontSee('Faire le test');
    });

    it('hides the section when there is neither a test nor a document', function () {
        $training = Training::factory()->create(['learning_test_url' => null]);

        $this->get($training->portalUrl())
            ->assertOk()
            ->assertDontSee('Pour aller plus loin');
    });
});

describe('documents', function () {
    beforeEach(function () {
        Storage::fake('local');
    });

    function documentUrl(Training $training, TrainingDocument $document): string
    {
        return route('portal.trainings.documents.show', ['publishedTraining' => $training->slug, 'document' => $document]);
    }

    it('lists documents in order with their description', function () {
        $training = Training::factory()->create();
        TrainingDocument::factory()->for($training)->withFile()->create([
            'title' => 'Fiche de vérification',
            'description' => "À imprimer.\nUne copie par salle.",
            'sort_order' => 2,
        ]);
        TrainingDocument::factory()->for($training)->withFile()->create(['title' => 'Guide complet', 'sort_order' => 1]);

        $this->get($training->portalUrl())
            ->assertSeeInOrder(['Guide complet', 'Fiche de vérification'])
            ->assertSee('À imprimer.<br />', escape: false)
            ->assertSee('Une copie par salle.');
    });

    it('downloads a document and records it for the employee', function () {
        $training = Training::factory()->create();
        $document = TrainingDocument::factory()->for($training)->withFile('guide.pdf')->create();

        $this->get(documentUrl($training, $document))->assertOk()->assertDownload('guide.pdf');
        $this->get(documentUrl($training, $document))->assertOk();

        expect(TrainingDocumentDownload::sole())
            ->employee_id->toBe($this->employee->id)
            ->training_id->toBe($training->id)
            ->training_document_id->toBe($document->id)
            ->download_count->toBe(2);

        $this->get($training->portalUrl())->assertSee('✓ Téléchargé');
    });

    it('refuses a document of another training', function () {
        $training = Training::factory()->create();
        $foreignDocument = TrainingDocument::factory()->withFile()->create();

        $this->get(documentUrl($training, $foreignDocument))->assertNotFound();

        expect(TrainingDocumentDownload::count())->toBe(0);
    });

    it('refuses a document of an unpublished training', function () {
        $training = Training::factory()->draft()->create();
        $document = TrainingDocument::factory()->for($training)->withFile()->create();

        $this->get(documentUrl($training, $document))->assertNotFound();
    });
});
