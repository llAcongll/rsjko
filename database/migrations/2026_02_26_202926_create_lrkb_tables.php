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
        Schema::create('lrkbs', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            $table->tinyInteger('triwulan');
            $table->date('tgl_rekonsiliasi')->nullable();
            $table->decimal('saldo_awal', 18, 2)->default(0);
            $table->decimal('pendapatan', 18, 2)->default(0);
            $table->decimal('belanja', 18, 2)->default(0);
            $table->decimal('pembiayaan', 18, 2)->default(0);
            $table->decimal('saldo_akhir_buku', 18, 2)->default(0);
            $table->decimal('saldo_fisik', 18, 2)->default(0);
            $table->decimal('saldo_bank', 18, 2)->default(0);
            $table->decimal('saldo_tunai', 18, 2)->default(0);
            $table->decimal('selisih', 18, 2)->default(0);
            $table->enum('status', ['draft', 'valid', 'dikunci'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['tahun', 'triwulan']);
        });

        Schema::create('lrkb_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lrkb_id')->constrained('lrkbs')->onDelete('cascade');
            $table->string('jenis'); // bank_penerimaan, bank_pengeluaran, tunai_penerimaan, tunai_pengeluaran
            $table->text('uraian');
            $table->decimal('jumlah', 18, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lrkb_details');
        Schema::dropIfExists('lrkbs');
    }
};
