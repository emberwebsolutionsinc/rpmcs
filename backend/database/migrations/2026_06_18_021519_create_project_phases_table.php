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
        Schema::create('project_phases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_project_id')
                ->constrained('property_projects')
                ->cascadeOnDelete();

            $table->string('phase_code');
            $table->string('phase_name');
            $table->text('description')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_project_id', 'phase_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_phases');
    }
};
