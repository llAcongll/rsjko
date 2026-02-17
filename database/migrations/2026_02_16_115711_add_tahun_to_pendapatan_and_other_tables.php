<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'pendapatan_umum',
            'pendapatan_bpjs',
            'pendapatan_jaminan',
            'pendapatan_lain',
            'pendapatan_kerjasama',
            'piutangs',
            'rekening_korans'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->integer('tahun')->nullable()->after('tanggal');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'pendapatan_umum',
            'pendapatan_bpjs',
            'pendapatan_jaminan',
            'pendapatan_lain',
            'pendapatan_kerjasama',
            'piutangs',
            'rekening_korans'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('tahun');
                });
            }
        }
    }
};
