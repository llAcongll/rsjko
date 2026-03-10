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
        Schema::create('arus_kas_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kode_rekening_id')->unique();
            $table->enum('tipe', ['OPERASI', 'INVESTASI', 'PENDANAAN']);
            $table->timestamps();

            $table->foreign('kode_rekening_id')->references('id')->on('kode_rekening')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arus_kas_mappings');
    }
};





