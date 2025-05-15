<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_dokumen', function (Blueprint $table) {
            $table->id(); // int unsigned auto_increment
            // Jika ingin int unsigned: $table->unsignedInteger('id')->autoIncrement();
            $table->string('nama_jenis', 100)->unique();
            $table->text('deskripsi')->nullable();
            // Tidak perlu timestamps jika ini data master
            // $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_dokumen');
    }
};
