<?php

use App\Models\Employee;
use App\Models\SiteSetting;
use App\Models\Training;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(Employee::factory()->create(), 'employee');
});

it('lists only published trainings as numbered modules in order', function () {
    Training::factory()->create(['title' => 'Qualité audio', 'sort_order' => 2]);
    Training::factory()->create(['title' => 'Ventes additionnelles', 'sort_order' => 1]);
    Training::factory()->draft()->create(['title' => 'Brouillon caché']);
    Training::factory()->archived()->create(['title' => 'Archive cachée']);

    $this->get(route('portal.home'))
        ->assertOk()
        ->assertSeeInOrder(['Module 1', 'Ventes additionnelles', 'Module 2', 'Qualité audio'])
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
