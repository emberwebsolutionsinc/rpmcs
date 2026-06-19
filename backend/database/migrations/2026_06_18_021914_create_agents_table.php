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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_agent_id')
                ->nullable()
                ->constrained('agents')
                ->nullOnDelete();

            $table->string('agent_code')->unique();

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();

            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            $table->string('license_number')->nullable();

            $table->decimal('default_commission_rate', 8, 2)->default(0);

            $table->enum('agent_type', [
                'main_agent',
                'sub_agent',
                'independent_agent',
            ])->default('independent_agent');

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
