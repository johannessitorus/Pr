<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_proyek_akhir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('jenis_dokumen_id')->constrained('jenis_dokumen')->onDelete('restrict');
            $table->string('nama_file_asli', 255);
            $table->string('nama_file_unik', 255);
            $table->string('file_path', 255);
            $table->integer('versi')->default(1);
            $table->text('catatan_mahasiswa')->nullable();
            $table->enum('status_review', ['pending', 'approved', 'revision_needed'])->default('pending');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // Reviewer adalah user (dosen)
            $table->timestamp('reviewed_at')->nullable();
            // $table->timestamps(); // uploaded_at sudah ada, reviewed_at sudah ada. Jika mau created_at & updated_at otomatis Laravel, tambahkan ini.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_proyek_akhir');
    }
};
