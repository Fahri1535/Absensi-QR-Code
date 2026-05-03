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
        Schema::create('bantuans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // 'operator' atau 'hrd'
            $table->string('nama');
            $table->string('deskripsi')->nullable();
            $table->string('telepon')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // Tambahkan tabel meta untuk jam operasional dan SLA
        Schema::create('bantuan_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bantuans');
    }
};
