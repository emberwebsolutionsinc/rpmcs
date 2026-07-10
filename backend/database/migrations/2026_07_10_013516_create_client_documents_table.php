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
    Schema::create('client_documents', function (Blueprint $table) {
        $table->id();

        $table->foreignId('client_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('document_type')->nullable();
        $table->string('document_name');
        $table->string('file_name');
        $table->string('file_path');
        $table->string('mime_type')->nullable();
        $table->unsignedBigInteger('file_size')->default(0);
        $table->text('remarks')->nullable();

        $table->foreignId('uploaded_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_documents');
    }
};
