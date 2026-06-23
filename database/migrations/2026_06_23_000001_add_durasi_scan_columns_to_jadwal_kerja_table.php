<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_kerja', function (Blueprint $table) {
            $table->unsignedSmallInteger('durasi_scan_masuk_menit')->default(60)->after('pulang_lebih_awal_menit');
            $table->unsignedSmallInteger('durasi_scan_pulang_menit')->default(60)->after('durasi_scan_masuk_menit');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_kerja', function (Blueprint $table) {
            $table->dropColumn(['durasi_scan_masuk_menit', 'durasi_scan_pulang_menit']);
        });
    }
};
