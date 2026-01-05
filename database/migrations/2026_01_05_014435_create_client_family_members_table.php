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
        Schema::create('client_family_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('relationship'); // spouse, child, parent, sibling, etc.

            $table->string('first_name');
            $table->string('last_name');

            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_family_members');
    }
};
