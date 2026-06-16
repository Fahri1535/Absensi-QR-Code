<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->unsignedTinyInteger('toleransi_menit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['jam_masuk', 'jam_pulang', 'toleransi_menit']);
        });
    }
};
