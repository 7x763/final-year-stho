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
        Schema::table('projects', function (Blueprint $table) {
            $table->longText('ai_analysis')->nullable();
            $table->dateTime('ai_analysis_at')->nullable();
            $table->string('ai_analysis_status')->default('idle'); // idle, processing, completed, failed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['ai_analysis', 'ai_analysis_at', 'ai_analysis_status']);
        });
    }
};
