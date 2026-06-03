<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cinemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('administrative_region_id')->nullable()->constrained('administrative_regions')->nullOnDelete();
            $table->string('name');
            $table->string('legal_company_name')->nullable();

            // Coordonnées
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Contact primaire
            $table->string('primary_contact_name')->nullable();
            $table->string('primary_contact_phone')->nullable();
            $table->string('primary_contact_email')->nullable();

            // Contact secondaire
            $table->string('secondary_contact_name')->nullable();
            $table->string('secondary_contact_phone')->nullable();
            $table->string('secondary_contact_email')->nullable();

            // Exploitation
            $table->string('pos_software')->nullable();
            $table->unsignedInteger('cash_registers_count')->nullable();
            $table->unsignedInteger('ticket_booths_count')->nullable();
            $table->boolean('alcohol_permit')->default(false);
            $table->string('edelivery')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cinemas');
    }
};
