<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Downloads now point to a training document (title, description and file) instead of the
 * raw media file. No download was recorded before this change, so none is carried over.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('training_document_downloads')->delete();

        // The new unique index is added first: MySQL needs an index starting with employee_id
        // to back its foreign key at all times.
        Schema::table('training_document_downloads', function (Blueprint $table) {
            $table->foreignId('training_document_id')->after('training_id')->constrained()->cascadeOnDelete();
            $table->unique(['employee_id', 'training_document_id'], 'training_document_downloads_employee_document_unique');
        });

        Schema::table('training_document_downloads', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'media_id']);
            $table->dropConstrainedForeignId('media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('training_document_downloads')->delete();

        Schema::table('training_document_downloads', function (Blueprint $table) {
            $table->foreignId('media_id')->after('training_id')->constrained('media')->cascadeOnDelete();
            $table->unique(['employee_id', 'media_id']);
        });

        Schema::table('training_document_downloads', function (Blueprint $table) {
            $table->dropUnique('training_document_downloads_employee_document_unique');
            $table->dropConstrainedForeignId('training_document_id');
        });
    }
};
