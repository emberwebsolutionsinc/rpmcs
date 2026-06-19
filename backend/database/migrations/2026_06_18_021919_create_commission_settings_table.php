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
        Schema::create('commission_settings', function (Blueprint $table) {
            $table->id();

            $table->decimal('default_commission_rate', 8, 2)->default(5);
            $table->decimal('sub_agent_share_percentage', 8, 2)->default(70);
            $table->decimal('main_agent_cut_percentage', 8, 2)->default(30);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_settings');
    }
};
