<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('training_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Starts with the questions previously written in the portal home page, so it looks the same after deployment.
        DB::table('training_faqs')->insert(collect([
            [
                'question' => 'Combien de temps dure le programme ?',
                'answer' => 'Chaque module dure quelques minutes. Vous pouvez les suivre à votre rythme, en une ou plusieurs fois : votre progression est enregistrée.',
            ],
            [
                'question' => 'Qui peut participer ?',
                'answer' => 'Les employés des cinémas membres de l’APCQ, sur invitation. Votre gestionnaire peut demander un accès pour vous auprès de l’APCQ.',
            ],
            [
                'question' => 'Que se passe-t-il si je manque une séance d’entraînement prévue ?',
                'answer' => 'Aucun problème : tous les modules restent accessibles en tout temps. Reprenez simplement la vidéo là où vous l’aviez laissée.',
            ],
        ])->map(fn (array $faq, int $index): array => [
            ...$faq,
            'is_published' => true,
            'sort_order' => $index + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_faqs');
    }
};
