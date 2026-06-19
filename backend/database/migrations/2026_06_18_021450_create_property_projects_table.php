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
    Schema::create('property_projects', function (Blueprint $table) {
        $table->id();

        $table->string('project_code')->unique();
        $table->string('project_name');
        $table->text('description')->nullable();

        $table->string('developer_name')->nullable();

        $table->string('country')->default('Philippines');
        $table->string('province')->nullable();
        $table->string('municipality')->nullable();
        $table->string('barangay')->nullable();
        $table->string('street')->nullable();

        $table->enum('status', ['active', 'inactive'])->default('active');

        $table->timestamps();
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::dropIfExists('property_projects');
}
};
