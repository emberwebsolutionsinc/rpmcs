<?php

use App\Models\Agent;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_commission_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Agent::class)
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignIdFor(Sale::class)
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->date('payment_date');

            $table->decimal('amount', 15, 2);

            $table->string('payment_method')->nullable();

            $table->string('reference_no')->nullable();

            $table->text('remarks')->nullable();

            $table->foreignIdFor(User::class, 'created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();

            $table->index('agent_id');
            $table->index('sale_id');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_commission_payments');
    }
};
