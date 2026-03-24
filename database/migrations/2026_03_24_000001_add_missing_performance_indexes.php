<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // users.type sering digunakan untuk filter role (manager, it, it_manager, user)
        Schema::table('users', function (Blueprint $table) {
            $table->index('type', 'users_type_index');
        });

        // Composite index untuk view IT/IT Manager: filter assignee_id + approval
        // Lebih efisien daripada index tunggal assignee_id saja
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['assignee_id', 'approval'], 'tickets_assignee_id_approval_index');
            $table->index(['approval', 'closed_at'], 'tickets_approval_closed_at_index');
        });

        // Composite index untuk query komentar per tiket dengan urutan waktu
        // Menggantikan kebutuhan index tunggal ticket_id (FK index) + sort created_at
        Schema::table('comments', function (Blueprint $table) {
            $table->index(['ticket_id', 'created_at'], 'comments_ticket_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_type_index');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_assignee_id_approval_index');
            $table->dropIndex('tickets_approval_closed_at_index');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_ticket_id_created_at_index');
        });
    }
};
