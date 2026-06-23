<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->boolean('is_generated')->default(false)->after('respondent_phone');
            $table->foreignId('generated_by')
                ->nullable()
                ->after('is_generated')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->dropForeign(['generated_by']);
            $table->dropColumn(['is_generated', 'generated_by']);
        });
    }
};
