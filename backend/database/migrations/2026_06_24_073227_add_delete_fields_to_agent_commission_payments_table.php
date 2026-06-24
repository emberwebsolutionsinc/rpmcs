<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_commission_payments', function (Blueprint $table) {
            $table->text('delete_reason')->nullable()->after('remarks');
            $table->foreignId('deleted_by')
                ->nullable()
                ->after('delete_reason')
                ->constrained('users')
                ->nullOnDelete();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('agent_commission_payments', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn('deleted_by');
            $table->dropColumn('delete_reason');
            $table->dropSoftDeletes();
        });
    }
};
