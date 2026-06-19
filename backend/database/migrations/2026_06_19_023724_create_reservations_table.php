<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            $table->string('reservation_no')->unique();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->foreignId('lot_id')
                ->constrained('lots')
                ->cascadeOnDelete();

            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('agents')
                ->nullOnDelete();

            $table->date('reservation_date');
            $table->date('expiration_date')->nullable();

            $table->decimal('reservation_fee', 12, 2)->default(0);

            $table->enum('status', [
                'active',
                'expired',
                'cancelled',
                'converted_to_sale',
            ])->default('active');

            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
