<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni', function (Blueprint $table) {
            $table->id('alumni_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('prodi_id')->constrained('prodis')->cascadeOnDelete();
            $table->string('nim', 20)->unique();
            $table->string('nama', 100);
            $table->string('email');
            $table->foreignId('year_id')->constrained('years')->cascadeOnDelete();
            $table->string('no_hp')->nullable();
            $table->enum('status', ['Bekerja', 'Mencari Kerja', 'Wirausaha', 'Studi Lanjut', 'Belum Bekerja'])->default('Belum Bekerja');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
