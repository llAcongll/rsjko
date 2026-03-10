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
        Schema::create('lo_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kode_rekening_id')->constrained('kode_rekening')->onDelete('cascade');
            $table->string('kategori'); // BEBAN_PEGAWAI, BEBAN_BARANG_JASA, BEBAN_PENYUSUTAN, BEBAN_LAINNYA, BEBAN_TRANSFER, PENDAPATAN_LO
            $table->timestamps();

            $table->unique('kode_rekening_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lo_mappings');
    }
};





