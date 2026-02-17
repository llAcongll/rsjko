<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pendapatan_jaminan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_pasien');
            $table->foreignId('ruangan_id')->constrained('ruangans')->cascadeOnUpdate()->restrictOnDelete();

            // Transaksi (Nama Perusahaan)
            $table->string('transaksi', 150)->nullable();

            $table->enum('metode_pembayaran', ['TUNAI', 'NON_TUNAI']);
            $table->string('bank', 50)->nullable();
            $table->string('metode_detail')->nullable();

            $table->unsignedBigInteger('rs_tindakan')->default(0);
            $table->unsignedBigInteger('rs_obat')->default(0);
            $table->unsignedBigInteger('pelayanan_tindakan')->default(0);
            $table->unsignedBigInteger('pelayanan_obat')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendapatan_jaminan');
    }
};
