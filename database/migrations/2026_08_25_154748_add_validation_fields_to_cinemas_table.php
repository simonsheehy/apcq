<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cinemas', function (Blueprint $table) {
            $table->uuid('access_token')->nullable()->after('id');
            $table->timestamp('personal_info_validated_at')->nullable()->after('edelivery');
            $table->timestamp('cinema_info_validated_at')->nullable()->after('personal_info_validated_at');
        });

        foreach (DB::table('cinemas')->orderBy('id')->get() as $cinema) {
            DB::table('cinemas')
                ->where('id', $cinema->id)
                ->update(['access_token' => (string) Str::uuid()]);
        }

        Schema::table('cinemas', function (Blueprint $table) {
            $table->unique('access_token');
        });
    }

    public function down(): void
    {
        Schema::table('cinemas', function (Blueprint $table) {
            $table->dropUnique(['access_token']);
            $table->dropColumn([
                'access_token',
                'personal_info_validated_at',
                'cinema_info_validated_at',
            ]);
        });
    }
};
