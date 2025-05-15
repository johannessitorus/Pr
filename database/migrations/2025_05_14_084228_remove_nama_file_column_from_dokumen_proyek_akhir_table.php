<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumen_proyek_akhir', function (Blueprint $table) {
            if (Schema::hasColumn('dokumen_proyek_akhir', 'nama_file')) {
                $table->dropColumn('nama_file');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dokumen_proyek_akhir', function (Blueprint $table) {
            // Jika ingin bisa rollback, tambahkan kembali kolomnya
            // Pastikan definisi kolomnya sama seperti sebelumnya
            if (!Schema::hasColumn('dokumen_proyek_akhir', 'nama_file')) {
                $table->string('nama_file')->after('jenis_dokumen_id'); // Sesuaikan posisi dan definisi
            }
        });
    }
};
