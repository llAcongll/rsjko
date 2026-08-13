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
        Schema::create('safe_space_screenings', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->integer('score')->nullable();
            $table->string('anxiety_result')->nullable();
            $table->string('depression_result')->nullable();
            $table->string('safety_answer')->nullable();
            $table->string('safety_status')->nullable();
            $table->string('follow_up')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safe_space_screenings');
    }
};
