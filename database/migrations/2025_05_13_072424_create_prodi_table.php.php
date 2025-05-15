<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prodi', function (Blueprint $table) {
            $table->id(); // int unsigned auto_increment primary key (default `id()` adalah bigint, bisa diubah jika perlu)
            // Jika ingin int unsigned: $table->unsignedInteger('id')->autoIncrement();
            $table->string('kode_prodi', 20)->unique();
            $table->string('nama_prodi', 100);
            $table->string('fakultas', 100)->nullable();
            // Tidak perlu timestamps jika ini data master yang jarang berubah
            // $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prodi');
    }
};
