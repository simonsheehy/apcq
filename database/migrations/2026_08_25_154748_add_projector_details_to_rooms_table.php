<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('projector_brand')->nullable()->after('projector');
            $table->string('projector_brand_other')->nullable()->after('projector_brand');
            $table->string('projector_model')->nullable()->after('projector_brand_other');
            $table->string('projector_serial_number')->nullable()->after('projector_model');
            $table->unsignedSmallInteger('projector_manufacture_year')->nullable()->after('projector_serial_number');
            $table->string('projection_type')->nullable()->after('projector_manufacture_year');
            $table->unsignedSmallInteger('installation_year')->nullable()->after('projection_type');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'projector_brand',
                'projector_brand_other',
                'projector_model',
                'projector_serial_number',
                'projector_manufacture_year',
                'projection_type',
                'installation_year',
            ]);
        });
    }
};
