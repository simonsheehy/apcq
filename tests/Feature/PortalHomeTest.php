<?php

use App\Models\Employee;
use App\Models\SiteSetting;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(Employee::factory()->create(), 'employee');
});

it('lists only published trainings in module and capsule order', function () {
    Training::factory()->create(['title' => 'Qualité audio', 'module_number' => 2, 'capsule_number' => 1]);
    Training::factory()->create(['title' => 'Ventes additionnelles', 'module_number' => 1, 'capsule_number' => 2]);
    Training::factory()->create(['title' => 'Intervenir sans escalader', 'module_number' => 1, 'capsule_number' => 1]);
    Training::factory()->draft()->create(['title' => 'Brouillon caché']);
    Training::factory()->archived()->create(['title' => 'Archive cachée']);

    $this->get(route('portal.home'))
        ->assertOk()
        ->assertSeeInOrder([
            'Module 1 · Capsule 1', 'Intervenir sans escalader',
            'Module 1 · Capsule 2', 'Ventes additionnelles',
            'Module 2 · Capsule 1', 'Qualité audio',
        ])
        ->assertDontSee('Brouillon caché')
        ->assertDontSee('Archive cachée');
});

it('shows an empty state when no training is published', function () {
    $this->get(route('portal.home'))
        ->assertOk()
        ->assertSee('Aucune formation n’est disponible pour le moment.');
});

it('shows the contact email from the site settings', function () {
    SiteSetting::instance()->update(['footer_email' => 'formation@apcq.ca']);

    $this->get(route('portal.home'))
        ->assertOk()
        ->assertSee('mailto:formation@apcq.ca', escape: false);
});
