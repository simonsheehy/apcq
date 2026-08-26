<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'projector_serial_number',
                'projector_manufacture_year',
            ]);
            $table->string('server_model')->nullable()->after('projector_model');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('server_model');
            $table->string('projector_serial_number')->nullable()->after('projector_model');
            $table->unsignedSmallInteger('projector_manufacture_year')->nullable()->after('projector_serial_number');
        });
    }
};
