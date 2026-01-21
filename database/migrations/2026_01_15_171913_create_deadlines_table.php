<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deadlines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visa_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('document_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type'); // interview, submission, document_reupload, etc
            $table->string('title');

            $table->date('due_date');

            $table->json('reminder_offsets')
                ->nullable(); // e.g. [7,3,1]

            $table->enum('status', [
                'pending',
                'completed',
                'missed',
            ])->default('pending');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['visa_application_id', 'status']);
            $table->index(['due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deadlines');
    }
};
