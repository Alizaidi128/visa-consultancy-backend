<?php
// database/migrations/xxxx_xx_xx_create_visa_types_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visa_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('code'); // F1, EB2-NIW, SWV
            $table->string('name'); // Official name
            $table->timestamps();

            $table->unique(['country_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_types');
    }
};
