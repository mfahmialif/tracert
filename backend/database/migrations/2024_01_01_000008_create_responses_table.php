<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->onDelete('cascade');
            $table->foreignId('alumni_id')->constrained('alumni', 'alumni_id')->onDelete('cascade');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // Ensure one response per alumni per questionnaire
            $table->unique(['questionnaire_id', 'alumni_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
