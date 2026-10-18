<?php

namespace Database\Factories;

use App\Models\Training;
use App\Models\TrainingDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends Factory<TrainingDocument>
 */
class TrainingDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_id' => Training::factory(),
            'title' => ucfirst(fake()->words(3, true)),
            'description' => fake()->sentence(12),
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    public function withFile(string $fileName = 'guide.pdf'): static
    {
        return $this->afterCreating(function (TrainingDocument $document) use ($fileName): void {
            $document->addMedia(UploadedFile::fake()->create($fileName, 10, 'application/pdf'))
                ->toMediaCollection('file');
        });
    }
}
