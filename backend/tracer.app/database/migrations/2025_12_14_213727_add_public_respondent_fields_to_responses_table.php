<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            // Make alumni_id nullable for public responses
            $table->foreignId('alumni_id')->nullable()->change();

            // Add public respondent fields
            $table->string('respondent_name')->nullable()->after('alumni_id');
            $table->string('respondent_email')->nullable()->after('respondent_name');
            $table->string('respondent_phone')->nullable()->after('respondent_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->dropColumn(['respondent_name', 'respondent_email', 'respondent_phone']);
            $table->foreignId('alumni_id')->nullable(false)->change();
        });
    }
};
