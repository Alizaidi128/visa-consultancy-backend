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
        Schema::create('visa_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('country');
            $table->string('visa_type');

            $table->enum('status', [
                'draft',
                'documents_pending',
                'submitted',
                'approved',
                'rejected'
            ])->default('draft');

            $table->date('submission_date')->nullable();
            $table->date('decision_date')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_applications');
    }
};
