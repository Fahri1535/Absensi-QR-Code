<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_kerja', function (Blueprint $table) {
            $table->unsignedTinyInteger('masuk_lebih_awal_menit')->default(15)->after('toleransi_menit');
            $table->unsignedTinyInteger('pulang_lebih_awal_menit')->default(30)->after('masuk_lebih_awal_menit');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_kerja', function (Blueprint $table) {
            $table->dropColumn(['masuk_lebih_awal_menit', 'pulang_lebih_awal_menit']);
        });
    }
};
