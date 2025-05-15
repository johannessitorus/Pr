<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('mahasiswa_terkait_id')->nullable()->constrained('mahasiswa')->onDelete('set null');
            $table->foreignId('dosen_terkait_id')->nullable()->constrained('dosen')->onDelete('set null');
            $table->string('activity_type', 100);
            $table->text('description');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // Tidak perlu updated_at untuk log biasanya
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_activities');
    }
};
