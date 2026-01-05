<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visa_application_family_member', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visa_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('client_family_member_id')
                ->constrained()
                ->cascadeOnDelete();

            // Optional: role in visa
            $table->enum('role', ['primary', 'dependent'])
                ->default('dependent');

            $table->timestamps();

            // Prevent duplicates
            $table->unique([
                'visa_application_id',
                'client_family_member_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_application_family_member');
    }
};
