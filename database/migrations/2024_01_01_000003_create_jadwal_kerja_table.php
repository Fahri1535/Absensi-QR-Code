<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris saja — setting global perusahaan
        Schema::create('jadwal_kerja', function (Blueprint $table) {
            $table->id();
            $table->time('jam_masuk')->default('08:00:00');
            $table->time('jam_pulang')->default('17:00:00');
            $table->unsignedTinyInteger('toleransi_menit')->default(5);
            $table->string('hari_kerja')->default('Senin - Jumat');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_kerja');
    }
};
