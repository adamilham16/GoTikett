<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('name');
            $table->string('password');
            $table->enum('type', ['it', 'manager', 'user']); // it=IT SIM, manager=Dept Head, user=Requester
            $table->string('role')->default('Staff');         // IT Infra, IT BA, ALL, Manager, Staff
            $table->string('dept')->default('IT');
            $table->string('color')->default('#60a5fa');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
