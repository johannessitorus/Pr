<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->unique();
            $table->string('nim', 20)->unique();
            $table->foreignId('prodi_id')->constrained('prodi')->onDelete('restrict'); // atau onDelete('set null')->nullable()
            $table->year('angkatan');
            $table->string('nomor_kelompok', 50)->nullable();
            $table->foreignId('dosen_pembimbing_id')->nullable()->constrained('users')->onDelete('set null'); // Dosen pembimbing adalah user
            $table->text('judul_proyek_akhir')->nullable();
            $table->enum('status_proyek_akhir', ['belum_ada', 'pengajuan_judul', 'bimbingan', 'selesai', 'revisi'])->default('belum_ada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
