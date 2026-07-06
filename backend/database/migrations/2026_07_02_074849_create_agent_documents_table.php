<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agent_id')
                ->constrained('agents')
                ->cascadeOnDelete();

            $table->string('document_type');
            $table->string('document_title');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);

            $table->date('expires_at')->nullable();
            $table->string('verification_status')->default('pending');
            $table->text('remarks')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['agent_id', 'document_type']);
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_documents');
    }
};
