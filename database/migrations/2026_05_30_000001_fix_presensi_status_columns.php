<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            if (!Schema::hasColumn('presensi', 'status_masuk')) {
                $table->enum('status_masuk', ['tepat_waktu', 'terlambat'])->nullable();
            }
            if (!Schema::hasColumn('presensi', 'status_pulang')) {
                $table->enum('status_pulang', ['normal', 'lebih_awal'])->nullable();
            }
        });
    }

    public function down(): void
    {
        // Tidak perlu rollback
    }
};
