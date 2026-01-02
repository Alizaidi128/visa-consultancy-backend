<?php
// database/migrations/xxxx_xx_xx_update_required_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('required_documents', function (Blueprint $table) {
            if (Schema::hasColumn('required_documents', 'country')) {
                $table->dropColumn('country');
            }

            if (Schema::hasColumn('required_documents', 'visa_type')) {
                $table->dropColumn('visa_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('required_documents', function (Blueprint $table) {
            $table->string('country')->nullable();
            $table->string('visa_type')->nullable();
        });
    }
};
