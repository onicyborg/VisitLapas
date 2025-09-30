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
        Schema::create('visit_queue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('visit_date');
            $table->string('ticket_number');

            $table->uuid('visitor_id');
            $table->uuid('inmate_id');

            $table->enum('status', ['waiting','called','serving','done','no_show','cancelled'])->default('waiting');
            $table->integer('priority')->default(0);

            $table->uuid('counter_id')->nullable();
            $table->uuid('created_by')->nullable();

            $table->timestampTz('called_at')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('ended_at')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            // Foreign keys
            $table->foreign('visitor_id')->references('id')->on('visitors')->cascadeOnDelete();
            $table->foreign('inmate_id')->references('id')->on('inmates')->cascadeOnDelete();
            $table->foreign('counter_id')->references('id')->on('counters')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            // Indexes
            $table->unique(['visit_date', 'ticket_number']);
            $table->index(['visit_date', 'status']);
            $table->index('visitor_id');
            $table->index('inmate_id');
            $table->index(['status', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_queue');
    }
};
