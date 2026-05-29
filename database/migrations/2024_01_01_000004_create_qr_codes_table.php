<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // QR permanen yang ditempel di kantor (masuk & pulang)
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['masuk', 'pulang']);
            $table->string('kode_qr')->unique();   // random string panjang
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
