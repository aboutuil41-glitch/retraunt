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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('label')->nullable();
            $table->text('warning_message')->nullable();
            $table->enum('status', ['processing', 'ready'])->default('processing');
            $table->timestamps();
            $table->unique(['user_id', 'dish_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
