<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToRequestBimbinganTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('request_bimbingan', function (Blueprint $table) {
            $table->text('catatan_dosen')->nullable()->after('catatan');
            $table->date('tanggal_dosen')->nullable()->after('tanggal_usulan');
            $table->time('jam_dosen')->nullable()->after('jam_usulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_bimbingan', function (Blueprint $table) {
            $table->dropColumn(['catatan_dosen', 'tanggal_dosen', 'jam_dosen']);
        });
    }
}
