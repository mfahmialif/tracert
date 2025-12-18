<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('type', ['text', 'textarea', 'number', 'radio', 'checkbox', 'select', 'scale', 'date']);
            $table->json('options')->nullable(); // For radio, checkbox, select, scale
            $table->boolean('is_required')->default(true);
            $table->integer('order')->default(0);
            $table->integer('section')->default(1); // For multi-step forms
            $table->foreignId('depends_on')->nullable()->constrained('questions')->onDelete('set null');
            $table->string('depends_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
