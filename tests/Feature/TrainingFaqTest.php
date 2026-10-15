<?php

use App\Filament\Resources\TrainingFaqs\Pages\CreateTrainingFaq;
use App\Filament\Resources\TrainingFaqs\Pages\ListTrainingFaqs;
use App\Models\Employee;
use App\Models\TrainingFaq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('administration', function () {
    beforeEach(function () {
        TrainingFaq::query()->delete();
        $this->actingAs(User::factory()->create());
    });

    it('lists questions in their display order', function () {
        $second = TrainingFaq::factory()->create(['sort_order' => 2]);
        $first = TrainingFaq::factory()->unpublished()->create(['sort_order' => 1]);

        Livewire::test(ListTrainingFaqs::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$first, $second], inOrder: true);
    });

    it('adds a new question at the end of the list', function () {
        TrainingFaq::factory()->create(['sort_order' => 7]);

        Livewire::test(CreateTrainingFaq::class)
            ->fillForm([
                'question' => 'Puis-je suivre les modules sur mon téléphone ?',
                'answer' => 'Oui, le Centre d’expertise fonctionne sur téléphone.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(TrainingFaq::latest('id')->first())
            ->sort_order->toBe(8)
            ->is_published->toBeTrue();
    });

    it('requires a question and an answer', function () {
        Livewire::test(CreateTrainingFaq::class)
            ->fillForm(['question' => '', 'answer' => ''])
            ->call('create')
            ->assertHasFormErrors(['question' => 'required', 'answer' => 'required']);
    });

    it('unpublishes a question from the list', function () {
        $faq = TrainingFaq::factory()->create();

        Livewire::test(ListTrainingFaqs::class)
            ->call('updateTableColumnState', 'is_published', (string) $faq->getKey(), false);

        expect($faq->refresh()->is_published)->toBeFalse();
    });
});

describe('portal home', function () {
    beforeEach(function () {
        TrainingFaq::query()->delete();
        $this->actingAs(Employee::factory()->create(), 'employee');
    });

    it('shows published questions in order', function () {
        TrainingFaq::factory()->create(['question' => 'Deuxième question ?', 'sort_order' => 2]);
        TrainingFaq::factory()->create(['question' => 'Première question ?', 'sort_order' => 1]);
        TrainingFaq::factory()->unpublished()->create(['question' => 'Question cachée ?']);

        $this->get(route('portal.home'))
            ->assertSeeInOrder(['FAQs', 'Première question ?', 'Deuxième question ?'])
            ->assertDontSee('Question cachée ?');
    });

    it('hides the section when no question is published', function () {
        TrainingFaq::factory()->unpublished()->create();

        $this->get(route('portal.home'))
            ->assertOk()
            ->assertDontSee('FAQs');
    });

    it('escapes answers while keeping line breaks', function () {
        TrainingFaq::factory()->create(['answer' => "Première ligne\n<script>alert(1)</script>"]);

        $this->get(route('portal.home'))
            ->assertSee('Première ligne<br />', escape: false)
            ->assertDontSee('<script>alert(1)</script>', escape: false);
    });
});

it('starts with the questions previously written in the page', function () {
    expect(TrainingFaq::ordered()->pluck('question')->all())->toBe([
        'Combien de temps dure le programme ?',
        'Qui peut participer ?',
        'Que se passe-t-il si je manque une séance d’entraînement prévue ?',
    ]);
});
