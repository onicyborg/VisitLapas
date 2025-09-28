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
        Schema::create('visitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('national_id', 32)->nullable()->unique();
            $table->string('name', 150);
            $table->enum('gender', ['male','female','other'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('relation_note', 120)->nullable();
            $table->text('photo_url')->nullable();
            $table->boolean('is_blacklisted')->default(false);
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index('name');
            $table->index('national_id');
            $table->index('is_blacklisted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
