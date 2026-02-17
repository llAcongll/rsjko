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
        Schema::create('anggaran_rincian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggaran_rekening_id')->constrained('anggaran_rekening')->onDelete('cascade');
            $table->string('uraian');
            $table->decimal('volume', 15, 2);
            $table->string('satuan');
            $table->decimal('tarif', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggaran_rincian');
    }
};
