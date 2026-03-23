<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100);
            $table->string('ip_address', 45);
            $table->enum('status', ['success', 'failed', 'locked']);
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['ip_address', 'created_at']);
            $table->index(['username', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
