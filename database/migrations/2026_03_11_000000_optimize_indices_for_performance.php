<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Optimization for RSJKO database performance.
     */
    public function up(): void
    {
        // 1. Revenue Masters
        Schema::table('revenue_masters', function (Blueprint $table) {
            $table->index('is_posted');
        });

        // 2. Rekening Koran
        Schema::table('rekening_korans', function (Blueprint $table) {
            $table->index(['bank', 'tahun', 'tanggal']);
            $table->index('cd');
        });

        // 3. Pendapatan (Child Tables)
        $pendapatanTables = [
            'pendapatan_umum',
            'pendapatan_bpjs',
            'pendapatan_jaminan',
            'pendapatan_lain',
            'pendapatan_kerjasama'
        ];

        foreach ($pendapatanTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->index(['revenue_master_id', 'tahun', 'tanggal'], "{$tbl}_rekon_index");
                $table->index(['ruangan_id', 'tahun', 'tanggal'], "{$tbl}_room_idx");
            });
        }

        // 4. Expenditures
        Schema::table('expenditures', function (Blueprint $table) {
            $table->index(['spending_date', 'spending_type']);
        });

        // 5. Penyesuaian
        Schema::table('penyesuaian_pendapatans', function (Blueprint $table) {
            $table->index(['kategori', 'tahun', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revenue_masters', function (Blueprint $table) {
            $table->dropIndex(['is_posted']);
        });

        Schema::table('rekening_korans', function (Blueprint $table) {
            $table->dropIndex(['bank', 'tahun', 'tanggal']);
            $table->dropIndex(['cd']);
        });

        $pendapatanTables = [
            'pendapatan_umum',
            'pendapatan_bpjs',
            'pendapatan_jaminan',
            'pendapatan_lain',
            'pendapatan_kerjasama'
        ];

        foreach ($pendapatanTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->dropIndex("{$tbl}_rekon_index");
                $table->dropIndex("{$tbl}_room_idx");
            });
        }

        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropIndex(['spending_date', 'spending_type']);
        });

        Schema::table('penyesuaian_pendapatans', function (Blueprint $table) {
            $table->dropIndex(['kategori', 'tahun', 'tanggal']);
        });
    }
};
