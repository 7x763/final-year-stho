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
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('epic_id', 'idx_tickets_epic');
        });

        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->index('ticket_id', 'idx_ticket_comments_ticket');
            $table->index('user_id', 'idx_ticket_comments_user');
        });

        Schema::table('ticket_histories', function (Blueprint $table) {
            $table->index('ticket_id', 'idx_ticket_histories_ticket');
            $table->index('user_id', 'idx_ticket_histories_user');
            $table->index('ticket_status_id', 'idx_ticket_histories_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_epic');
        });

        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropIndex('idx_ticket_comments_ticket');
            $table->dropIndex('idx_ticket_comments_user');
        });

        Schema::table('ticket_histories', function (Blueprint $table) {
            $table->dropIndex('idx_ticket_histories_ticket');
            $table->dropIndex('idx_ticket_histories_user');
            $table->dropIndex('idx_ticket_histories_status');
        });
    }
};
