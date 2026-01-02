<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Check FK exists before dropping
            $foreignKeys = collect(
                DB::select(
                    "SELECT CONSTRAINT_NAME
                     FROM information_schema.KEY_COLUMN_USAGE
                     WHERE TABLE_SCHEMA = DATABASE()
                       AND TABLE_NAME = 'users'
                       AND COLUMN_NAME = 'approved_by'"
                )
            )->pluck('CONSTRAINT_NAME');

            if ($foreignKeys->contains('users_approved_by_foreign')) {
                $table->dropForeign('users_approved_by_foreign');
            }
        });
    }

    public function down(): void
    {
        // NO rollback needed — column already exists
    }
};
