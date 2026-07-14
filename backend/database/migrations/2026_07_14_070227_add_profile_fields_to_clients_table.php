<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'gender')) {
                $table->string('gender', 30)
                    ->nullable();
            }

            if (!Schema::hasColumn('clients', 'nationality')) {
                $table->string('nationality', 100)
                    ->nullable();
            }

            if (!Schema::hasColumn('clients', 'occupation')) {
                $table->string('occupation', 150)
                    ->nullable();
            }

            if (!Schema::hasColumn('clients', 'employer')) {
                $table->string('employer', 150)
                    ->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('clients', 'gender')) {
                $columns[] = 'gender';
            }

            if (Schema::hasColumn('clients', 'nationality')) {
                $columns[] = 'nationality';
            }

            if (Schema::hasColumn('clients', 'occupation')) {
                $columns[] = 'occupation';
            }

            if (Schema::hasColumn('clients', 'employer')) {
                $columns[] = 'employer';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
