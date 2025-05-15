<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_bimbingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('users')->onDelete('cascade'); // Dosen adalah user
            $table->foreignId('request_bimbingan_id')->nullable()->constrained('request_bimbingan')->onDelete('set null');
            $table->dateTime('tanggal_bimbingan');
            $table->string('topik', 255);
            $table->text('catatan_mahasiswa')->nullable();
            $table->text('catatan_dosen')->nullable();
            $table->unsignedInteger('pertemuan_ke')->nullable();
            $table->enum('status_kehadiran', ['hadir', 'tidak_hadir_mahasiswa', 'tidak_hadir_dosen'])->default('hadir');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_bimbingan');
    }
};
