<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();

            $table->string('collection_no')->unique();

            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->foreignId('payment_schedule_id')
                ->nullable()
                ->constrained('payment_schedules')
                ->nullOnDelete();

            $table->string('official_receipt_no')->nullable()->unique();

            $table->date('payment_date');

            $table->decimal('amount_paid', 12, 2);

            $table->enum('payment_method', [
                'cash',
                'check',
                'bank_transfer',
                'gcash',
                'maya',
                'other',
            ])->default('cash');

            $table->string('reference_no')->nullable();

            $table->enum('status', [
                'posted',
                'voided',
            ])->default('posted');

            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
