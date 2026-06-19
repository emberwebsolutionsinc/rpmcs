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
        Schema::create('project_blocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_project_id')
                ->constrained('property_projects')
                ->cascadeOnDelete();

            $table->foreignId('project_phase_id')
                ->nullable()
                ->constrained('project_phases')
                ->nullOnDelete();

            $table->string('block_no');
            $table->text('description')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'property_project_id',
                'project_phase_id',
                'block_no',
            ], 'unique_block_per_phase');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_blocks');
    }
};
