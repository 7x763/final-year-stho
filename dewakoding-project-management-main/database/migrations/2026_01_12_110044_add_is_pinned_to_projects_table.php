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
            $table->boolean('is_pinned')->default(false)->after('pinned_date');
        });

        // Initialize is_pinned based on pinned_date
        DB::table('projects')
            ->whereNotNull('pinned_date')
            ->update(['is_pinned' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('is_pinned');
        });
    }
};
