<?php
// database/migrations/xxxx_xx_xx_create_visa_type_required_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visa_type_required_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visa_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('required_document_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->unique(
                ['visa_type_id', 'required_document_id'],
                'visa_type_doc_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_type_required_documents');
    }
};
