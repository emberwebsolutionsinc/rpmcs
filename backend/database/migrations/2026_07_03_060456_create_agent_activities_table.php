<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agent_id')
                ->constrained('agents')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('activity_type');
            $table->string('title');
            $table->text('description')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['agent_id', 'activity_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_activities');
    }
};
