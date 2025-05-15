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
                // Mengubah agar bisa NULL
                $table->string('nama_file')->nullable()->change();
                // ATAU mengubah agar punya default value
                // $table->string('nama_file')->default('default_value_here')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dokumen_proyek_akhir', function (Blueprint $table) {
            if (Schema::hasColumn('dokumen_proyek_akhir', 'nama_file')) {
                // Kembalikan ke definisi NOT NULL jika sebelumnya begitu
                $table->string('nama_file')->nullable(false)->change();
            }
        });
    }
};
