<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_lengkap');
            $table->string('jabatan')->nullable();
            $table->string('nomor_telepon', 20)->nullable();
            $table->string('foto')->nullable();           // path storage
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->string('kode_karyawan')->unique()->nullable(); // untuk QR personal profil
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
