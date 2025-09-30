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
        Schema::create('inmates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('register_no', 50)->unique();
            $table->string('name', 150);
            $table->enum('gender', ['male','female','other']);
            $table->date('birth_date')->nullable();
            $table->string('cell_block', 50)->nullable();
            $table->enum('status', ['active','transferred','released'])->default('active');
            $table->text('notes')->nullable();
            $table->text('photo_url')->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inmates');
    }
};
