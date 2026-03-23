<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('freeze_status', ['pending_approval', 'active'])
                  ->nullable()
                  ->after('closed_at');
            $table->unsignedBigInteger('freeze_paused_seconds')
                  ->default(0)
                  ->after('freeze_status');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['freeze_status', 'freeze_paused_seconds']);
        });
    }
};
