<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_kerja', function (Blueprint $table) {
            $table->decimal('kantor_latitude', 10, 7)->nullable()->after('hari_kerja');
            $table->decimal('kantor_longitude', 10, 7)->nullable()->after('kantor_latitude');
            $table->unsignedInteger('radius_meter')->nullable()->after('kantor_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_kerja', function (Blueprint $table) {
            $table->dropColumn(['kantor_latitude', 'kantor_longitude', 'radius_meter']);
        });
    }
};
