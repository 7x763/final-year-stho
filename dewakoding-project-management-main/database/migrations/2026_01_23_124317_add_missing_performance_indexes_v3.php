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
            $table->index('project_id', 'idx_tickets_project');
            $table->index('ticket_status_id', 'idx_tickets_status');
            $table->index('priority_id', 'idx_tickets_priority');
            $table->index('created_by', 'idx_tickets_creator');
        });

        Schema::table('ticket_statuses', function (Blueprint $table) {
            $table->index('project_id', 'idx_ticket_statuses_project');
        });

        Schema::table('epics', function (Blueprint $table) {
            $table->index('project_id', 'idx_epics_project');
        });

        Schema::table('project_members', function (Blueprint $table) {
            $table->index('project_id', 'idx_project_members_project');
            $table->index('user_id', 'idx_project_members_user');
        });

        Schema::table('ticket_users', function (Blueprint $table) {
            $table->index('ticket_id', 'idx_ticket_users_ticket');
            $table->index('user_id', 'idx_ticket_users_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_project');
            $table->dropIndex('idx_tickets_status');
            $table->dropIndex('idx_tickets_priority');
            $table->dropIndex('idx_tickets_creator');
        });

        Schema::table('ticket_statuses', function (Blueprint $table) {
            $table->dropIndex('idx_ticket_statuses_project');
        });

        Schema::table('epics', function (Blueprint $table) {
            $table->dropIndex('idx_epics_project');
        });

        Schema::table('project_members', function (Blueprint $table) {
            $table->dropIndex('idx_project_members_project');
            $table->dropIndex('idx_project_members_user');
        });

        Schema::table('ticket_users', function (Blueprint $table) {
            $table->dropIndex('idx_ticket_users_ticket');
            $table->dropIndex('idx_ticket_users_user');
        });
    }
};
