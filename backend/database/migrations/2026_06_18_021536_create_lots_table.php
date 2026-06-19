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
    Schema::create('lots', function (Blueprint $table) {
        $table->id();

        $table->foreignId('property_project_id')
            ->constrained('property_projects')
            ->cascadeOnDelete();

        $table->foreignId('project_phase_id')
            ->nullable()
            ->constrained('project_phases')
            ->nullOnDelete();

        $table->foreignId('project_block_id')
            ->nullable()
            ->constrained('project_blocks')
            ->nullOnDelete();

        $table->string('lot_code')->unique();
        $table->string('lot_no');
        $table->string('title_no')->nullable();

        $table->decimal('lot_area', 12, 2);
        $table->decimal('price_per_sqm', 12, 2)->default(0);
        $table->decimal('selling_price', 12, 2)->default(0);

        $table->boolean('corner_lot')->default(false);
        $table->boolean('road_lot')->default(false);

        $table->enum('status', [
            'available',
            'reserved',
            'sold',
            'fully_paid',
            'cancelled',
        ])->default('available');

        $table->text('remarks')->nullable();

        $table->timestamps();
        $table->softDeletes();

        $table->unique([
            'property_project_id',
            'project_phase_id',
            'project_block_id',
            'lot_no',
        ], 'unique_lot_per_block');
    });
}

public function down(): void
{
    Schema::dropIfExists('lots');
}
};
