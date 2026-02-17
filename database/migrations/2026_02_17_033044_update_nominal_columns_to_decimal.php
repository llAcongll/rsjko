<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pendapatan_umum', function (Blueprint $table) {
            $table->decimal('rs_tindakan', 15, 2)->change();
            $table->decimal('rs_obat', 15, 2)->change();
            $table->decimal('pelayanan_tindakan', 15, 2)->change();
            $table->decimal('pelayanan_obat', 15, 2)->change();
            $table->decimal('total', 15, 2)->change();
        });

        Schema::table('pendapatan_bpjs', function (Blueprint $table) {
            $table->decimal('rs_tindakan', 15, 2)->change();
            $table->decimal('rs_obat', 15, 2)->change();
            $table->decimal('pelayanan_tindakan', 15, 2)->change();
            $table->decimal('pelayanan_obat', 15, 2)->change();
            $table->decimal('total', 15, 2)->change();
        });

        Schema::table('pendapatan_jaminan', function (Blueprint $table) {
            $table->decimal('rs_tindakan', 15, 2)->change();
            $table->decimal('rs_obat', 15, 2)->change();
            $table->decimal('pelayanan_tindakan', 15, 2)->change();
            $table->decimal('pelayanan_obat', 15, 2)->change();
            $table->decimal('total', 15, 2)->change();
        });

        Schema::table('pendapatan_lain', function (Blueprint $table) {
            $table->decimal('rs_tindakan', 15, 2)->change();
            $table->decimal('rs_obat', 15, 2)->change();
            $table->decimal('pelayanan_tindakan', 15, 2)->change();
            $table->decimal('pelayanan_obat', 15, 2)->change();
            $table->decimal('total', 15, 2)->change();
        });

        Schema::table('pendapatan_kerjasama', function (Blueprint $table) {
            $table->decimal('rs_tindakan', 15, 2)->change();
            $table->decimal('rs_obat', 15, 2)->change();
            $table->decimal('pelayanan_tindakan', 15, 2)->change();
            $table->decimal('pelayanan_obat', 15, 2)->change();
            $table->decimal('total', 15, 2)->change();
        });

        Schema::table('piutangs', function (Blueprint $table) {
            $table->decimal('jumlah_piutang', 15, 2)->change();
            $table->decimal('potongan', 15, 2)->change();
            $table->decimal('administrasi_bank', 15, 2)->change();
            $table->decimal('total_diterima', 15, 2)->change();
        });

        Schema::table('rekening_korans', function (Blueprint $table) {
            $table->decimal('jumlah', 15, 2)->change();
        });

        Schema::table('penyesuaian_pendapatans', function (Blueprint $table) {
            $table->decimal('potongan', 15, 2)->change();
            $table->decimal('administrasi_bank', 15, 2)->change();
        });
    }

    public function down(): void
    {
        // No down migration as converting back might lose precision if data was stored with decimals
    }
};
