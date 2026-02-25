<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spj', function (Blueprint $table) {
            $table->id();
            $table->string('spj_number')->unique();
            $table->date('spj_date');
            $table->foreignId('bendahara_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'VALID'])->default('DRAFT');
            $table->timestamps();
        });

        Schema::create('spj_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spj_id')->constrained('spj')->onDelete('cascade');
            $table->foreignId('expenditure_id')->constrained('expenditures')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spj_items');
        Schema::dropIfExists('spj');
    }
};
