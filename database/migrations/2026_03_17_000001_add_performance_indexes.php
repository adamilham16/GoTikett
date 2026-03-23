<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('approval');
            $table->index('closed_at');
            $table->index('created_at');
            $table->index('creator_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index('status');
            $table->index('ticket_id');
        });

        Schema::table('auto_assign_rules', function (Blueprint $table) {
            $table->index(['kategori', 'client']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['approval']);
            $table->dropIndex(['closed_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['creator_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['ticket_id']);
        });

        Schema::table('auto_assign_rules', function (Blueprint $table) {
            $table->dropIndex(['kategori', 'client']);
        });
    }
};
