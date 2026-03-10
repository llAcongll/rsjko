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
        Schema::create('neraca_manual_inputs', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->integer('bulan')->nullable(); // Bulan input (1-12), null if global year
            $table->string('account_key'); // persediaan, aset_tetap, utang_belanja, etc.
            $table->decimal('nominal', 18, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neraca_manual_inputs');
    }
};





