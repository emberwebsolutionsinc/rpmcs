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
    Schema::create('sales', function (Blueprint $table) {
        $table->id();

        $table->string('sale_no')->unique();

        $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
        $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
        $table->foreignId('lot_id')->constrained('lots')->restrictOnDelete();
        $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();

        $table->decimal('contract_price', 15, 2)->default(0);
        $table->decimal('downpayment', 15, 2)->default(0);
        $table->decimal('balance', 15, 2)->default(0);

        $table->date('sale_date');

        $table->enum('status', [
            'active',
            'cancelled',
            'completed',
        ])->default('active');

        $table->text('remarks')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
