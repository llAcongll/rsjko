<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('piutangs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');

            // Relasi ke Perusahaan (BPJS, Asuransi Lain, dll)
            $table->foreignId('perusahaan_id')
                ->constrained('perusahaans')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Periode Pelayanan (Teks bebas, e.g. "Januari 2024")
            $table->string('bulan_pelayanan', 50);

            // Nominal
            $table->unsignedBigInteger('jumlah_piutang')->default(0); // Tagihan Kotor
            $table->unsignedBigInteger('potongan')->default(0);       // Biaya/Potongan
            $table->unsignedBigInteger('administrasi_bank')->default(0); // Admin Bank
            $table->unsignedBigInteger('total_diterima')->default(0); // Netto (Piutang - Potongan - Admin)

            // Status
            $table->enum('status', ['LUNAS', 'BELUM_LUNAS'])->default('BELUM_LUNAS');

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piutangs');
    }
};
