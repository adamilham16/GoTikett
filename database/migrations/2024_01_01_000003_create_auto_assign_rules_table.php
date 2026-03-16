<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auto_assign_rules', function (Blueprint $table) {
            $table->id();
            $table->string('kategori');           // Infra, Sistem, Telko
            $table->string('client');             // nama client
            $table->foreignId('assignee_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_assign_rules');
    }
};
