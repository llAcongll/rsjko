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
        Schema::create('penyesuaian_pendapatans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->integer('tahun');
            $table->enum('kategori', ['BPJS', 'JAMINAN']);
            $table->foreignId('perusahaan_id')->constrained('perusahaans');
            $table->bigInteger('potongan')->default(0);
            $table->bigInteger('administrasi_bank')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyesuaian_pendapatans');
    }
};
