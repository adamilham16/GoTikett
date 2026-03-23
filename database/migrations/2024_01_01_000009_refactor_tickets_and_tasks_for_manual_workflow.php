<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Hapus kolom stage-based dari tickets (satu per satu untuk kompatibilitas MySQL)
        $dropCols = ['status', 'stage_log', 'stage_due', 'frozen', 'frozen_pct'];
        foreach ($dropCols as $col) {
            if (Schema::hasColumn('tickets', $col)) {
                Schema::table('tickets', function ($table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }

        // Tambah kolom notes ke tasks (due_date sudah ada dari migrasi sebelumnya)
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'notes')) {
                $table->text('notes')->nullable()->after('due_date');
            }
        });
    }

    public function down(): void
    {
        // Kembalikan kolom tasks
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        // Kembalikan kolom tickets
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('status', [
                'userreq', 'reqanalysis', 'sprintplanning',
                'development', 'sit', 'uat', 'deployment', 'golive'
            ])->default('userreq')->after('type');
            $table->boolean('frozen')->default(false)->after('closed_at');
            $table->integer('frozen_pct')->nullable()->after('frozen');
            $table->json('stage_log')->nullable()->after('frozen_pct');
            $table->json('stage_due')->nullable()->after('stage_log');
        });
    }
};
