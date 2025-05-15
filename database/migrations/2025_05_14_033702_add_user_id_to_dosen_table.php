<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
/*public function up()
{
    Schema::table('dosen', function (Blueprint $table) {
        // Menambahkan kolom user_id pada tabel dosen
        $table->unsignedBigInteger('user_id')->nullable(); // nullable jika kolom ini bisa kosong

        // Menambahkan foreign key constraint
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}*/

public function down()
{
    Schema::table('dosen', function (Blueprint $table) {
        // Menghapus kolom user_id dan foreign key constraint
        $table->dropForeign(['user_id']);  // Menghapus foreign key constraint
        $table->dropColumn('user_id');     // Menghapus kolom user_id
    });
}
};
