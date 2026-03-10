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
        Schema::create('treasurer_cash', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('type', ['TERIMA_UP', 'BELANJA_UP', 'GU']);
            $table->string('ref_table')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treasurer_cash');
    }
};





