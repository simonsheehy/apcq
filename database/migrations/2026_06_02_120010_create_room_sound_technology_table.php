<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_sound_technology', function (Blueprint $table) {
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('sound_technology_id')->constrained('sound_technologies')->cascadeOnDelete();

            $table->primary(['room_id', 'sound_technology_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_sound_technology');
    }
};
