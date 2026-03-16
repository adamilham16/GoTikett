<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id')->unique();   // TKT-001, TKT-002, ...
            $table->string('title');
            $table->text('desc')->nullable();
            $table->enum('type', ['incident', 'newproject', 'openrequest'])->default('openrequest');
            $table->enum('status', [
                'userreq', 'reqanalysis', 'sprintplanning',
                'development', 'sit', 'uat', 'deployment', 'golive'
            ])->default('userreq');
            $table->enum('approval', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('category')->nullable();  // Infra, Sistem, Telko
            $table->string('client')->nullable();
            $table->string('priority')->nullable();  // critical, high, medium, low
            $table->foreignId('creator_id')->constrained('users');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('frozen')->default(false);
            $table->integer('frozen_pct')->nullable();
            $table->json('stage_log')->nullable();   // { "userreq": "2024-01-01T...", ... }
            $table->json('stage_due')->nullable();   // { "reqanalysis": "2024-01-10", ... }
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
