<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            $table->foreignId('country_id')
                ->nullable()
                ->after('client_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('visa_type_id')
                ->nullable()
                ->after('country_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('visa_type_id');
            $table->dropConstrainedForeignId('country_id');
        });
    }
};
