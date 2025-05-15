<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_bimbingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('users')->onDelete('cascade'); // Dosen adalah user
            $table->date('tanggal_usulan');
            $table->time('jam_usulan');
            $table->string('lokasi_usulan', 255)->nullable();
            $table->text('topik_bimbingan');
            $table->enum('status_request', ['pending', 'approved', 'rejected', 'rescheduled'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_bimbingan');
    }
};
