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
        Schema::create('pengesahan_periodes', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('bulan');
            $table->smallInteger('tahun');
            $table->enum('status', ['draft', 'disahkan', 'terkunci'])->default('draft');
            $table->date('tgl_pengesahan')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['bulan', 'tahun']);
        });

        Schema::create('sp3bps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('pengesahan_periodes')->onDelete('cascade');
            $table->decimal('saldo_awal', 18, 2)->default(0);
            $table->decimal('pendapatan', 18, 2)->default(0);
            $table->decimal('belanja', 18, 2)->default(0);
            $table->decimal('pembiayaan_terima', 18, 2)->default(0);
            $table->decimal('pembiayaan_keluar', 18, 2)->default(0);
            $table->decimal('saldo_akhir', 18, 2)->default(0);
            $table->decimal('selisih', 18, 2)->default(0);
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->timestamps();
        });

        Schema::create('sp3bp_pendapatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sp3bp_id')->constrained('sp3bps')->onDelete('cascade');
            $table->string('kode_rekening');
            $table->string('uraian');
            $table->decimal('jumlah', 18, 2);
            $table->timestamps();
        });

        Schema::create('sp3bp_belanjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sp3bp_id')->constrained('sp3bps')->onDelete('cascade');
            $table->string('kode_rekening');
            $table->string('uraian');
            $table->decimal('jumlah', 18, 2);
            $table->timestamps();
        });

        Schema::create('sp3bp_rekonsiliasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sp3bp_id')->constrained('sp3bps')->onDelete('cascade');
            $table->decimal('saldo_bank', 18, 2)->default(0);
            $table->decimal('saldo_tunai', 18, 2)->default(0);
            $table->decimal('saldo_buku', 18, 2)->default(0);
            $table->decimal('selisih', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp3bp_rekonsiliasis');
        Schema::dropIfExists('sp3bp_belanjas');
        Schema::dropIfExists('sp3bp_pendapatans');
        Schema::dropIfExists('sp3bps');
        Schema::dropIfExists('pengesahan_periodes');
    }
};
