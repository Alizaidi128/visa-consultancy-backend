<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visa_application_id')->nullable()->constrained()->nullOnDelete();

            $table->date('scheduled_date');
            $table->time('from_time');
            $table->time('to_time');

            $table->string('status')->default('scheduled');
            // scheduled | completed | cancelled | no_show | rescheduled

            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['scheduled_date', 'from_time', 'to_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
