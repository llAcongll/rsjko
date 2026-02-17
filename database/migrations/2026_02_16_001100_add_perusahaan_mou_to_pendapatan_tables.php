<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // BPJS → perusahaan_id (fixed: BPJS Kesehatan)
        Schema::table('pendapatan_bpjs', function (Blueprint $table) {
            $table->foreignId('perusahaan_id')
                ->nullable()
                ->after('ruangan_id')
                ->constrained('perusahaans')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        // JAMINAN → perusahaan_id (banyak pilihan)
        Schema::table('pendapatan_jaminan', function (Blueprint $table) {
            $table->foreignId('perusahaan_id')
                ->nullable()
                ->after('ruangan_id')
                ->constrained('perusahaans')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        // KERJASAMA → mou_id
        Schema::table('pendapatan_kerjasama', function (Blueprint $table) {
            $table->foreignId('mou_id')
                ->nullable()
                ->after('ruangan_id')
                ->constrained('mous')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        // LAIN-LAIN → mou_id
        Schema::table('pendapatan_lain', function (Blueprint $table) {
            $table->foreignId('mou_id')
                ->nullable()
                ->after('ruangan_id')
                ->constrained('mous')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pendapatan_bpjs', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
            $table->dropColumn('perusahaan_id');
        });

        Schema::table('pendapatan_jaminan', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
            $table->dropColumn('perusahaan_id');
        });

        Schema::table('pendapatan_kerjasama', function (Blueprint $table) {
            $table->dropForeign(['mou_id']);
            $table->dropColumn('mou_id');
        });

        Schema::table('pendapatan_lain', function (Blueprint $table) {
            $table->dropForeign(['mou_id']);
            $table->dropColumn('mou_id');
        });
    }
};
