<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('dokumen_proyek_akhir', function (Blueprint $table) {
            if (!Schema::hasColumn('dokumen_proyek_akhir', 'created_at') && !Schema::hasColumn('dokumen_proyek_akhir', 'updated_at')) {
                $table->timestamps(); // Menambahkan created_at dan updated_at
            }
            // Tambahkan kolom lain dari $fillable jika belum ada
            if (!Schema::hasColumn('dokumen_proyek_akhir', 'nama_file_asli')) {
                $table->string('nama_file_asli')->after('jenis_dokumen_id'); // Sesuaikan posisi
            }
            if (!Schema::hasColumn('dokumen_proyek_akhir', 'nama_file_unik')) {
                $table->string('nama_file_unik')->after('nama_file_asli');
            }
            if (!Schema::hasColumn('dokumen_proyek_akhir', 'ekstensi_file')) {
                $table->string('ekstensi_file', 10)->after('file_path');
            }
            if (!Schema::hasColumn('dokumen_proyek_akhir', 'ukuran_file')) {
                $table->unsignedBigInteger('ukuran_file')->after('ekstensi_file');
            }
            if (!Schema::hasColumn('dokumen_proyek_akhir', 'catatan_reviewer')) {
                $table->text('catatan_reviewer')->nullable()->after('reviewed_at');
            }
            // Ganti nama kolom 'catatan' menjadi 'catatan_mahasiswa' jika perlu
            if (Schema::hasColumn('dokumen_proyek_akhir', 'catatan') && !Schema::hasColumn('dokumen_proyek_akhir', 'catatan_mahasiswa')) {
                $table->renameColumn('catatan', 'catatan_mahasiswa');
            } elseif (!Schema::hasColumn('dokumen_proyek_akhir', 'catatan_mahasiswa')  && !Schema::hasColumn('dokumen_proyek_akhir', 'catatan')) {
                 $table->text('catatan_mahasiswa')->nullable()->after('versi');
            }
            // Hapus kolom 'nama_file' jika Anda menggantinya dengan 'nama_file_asli' dan 'nama_file_unik'
            if (Schema::hasColumn('dokumen_proyek_akhir', 'nama_file') && Schema::hasColumn('dokumen_proyek_akhir', 'nama_file_asli')) {
                $table->dropColumn('nama_file');
            }
        });
    }
    public function down(): void {
        Schema::table('dokumen_proyek_akhir', function (Blueprint $table) {
            if (Schema::hasColumn('dokumen_proyek_akhir', 'created_at') && Schema::hasColumn('dokumen_proyek_akhir', 'updated_at')) {
                $table->dropTimestamps();
            }
            // Tambahkan dropColumn untuk kolom baru jika perlu untuk rollback
            // $table->dropColumn(['nama_file_asli', 'nama_file_unik', 'ekstensi_file', 'ukuran_file', 'catatan_reviewer']);
            // Jika Anda me-rename 'catatan', kembalikan namanya
            // if (Schema::hasColumn('dokumen_proyek_akhir', 'catatan_mahasiswa')) {
            //     $table->renameColumn('catatan_mahasiswa', 'catatan');
            // }
        });
    }
};
