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
        Schema::create('queue_call_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('queue_id');
            $table->uuid('called_by')->nullable();
            $table->uuid('counter_id')->nullable();
            $table->integer('call_no');
            $table->string('message', 200)->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('queue_id')->references('id')->on('visit_queue')->cascadeOnDelete();
            $table->foreign('called_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('counter_id')->references('id')->on('counters')->nullOnDelete();

            $table->unique(['queue_id', 'call_no']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_call_logs');
    }
};

