<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prodis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('strata', ['S1', 'S2', 'S3'])->default('S1');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prodis');
    }
};
