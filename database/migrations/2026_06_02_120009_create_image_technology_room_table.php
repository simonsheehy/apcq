<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_technology_room', function (Blueprint $table) {
            $table->foreignId('image_technology_id')->constrained('image_technologies')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();

            $table->primary(['image_technology_id', 'room_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_technology_room');
    }
};
