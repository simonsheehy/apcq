<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trainings are now ordered by their module and capsule numbers instead of a manual order.
 * Existing trainings keep their order: each becomes its own module, as capsule 1.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->unsignedSmallInteger('module_number')->default(1)->after('slug');
            $table->unsignedSmallInteger('capsule_number')->default(1)->after('module_number');
        });

        DB::table('trainings')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->each(fn (int $id, int $index) => DB::table('trainings')
                ->where('id', $id)
                ->update(['module_number' => $index + 1]));

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn('sort_order');
            $table->index(['module_number', 'capsule_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0);
        });

        DB::table('trainings')
            ->orderBy('module_number')
            ->orderBy('capsule_number')
            ->orderBy('id')
            ->pluck('id')
            ->each(fn (int $id, int $index) => DB::table('trainings')
                ->where('id', $id)
                ->update(['sort_order' => $index + 1]));

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropIndex(['module_number', 'capsule_number']);
            $table->dropColumn(['module_number', 'capsule_number']);
        });
    }
};
