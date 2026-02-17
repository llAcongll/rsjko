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
        Schema::table('kode_rekening', function (Blueprint $table) {
            $table->enum('sumber_data', [
                'UMUM',
                'BPJS',
                'JAMINAN',
                'KERJASAMA',
                'LAIN',
                'SEWA',
                'PENDIDIKAN',
                'LAINNYA',
                'USAHA'
            ])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kode_rekening', function (Blueprint $table) {
            $table->enum('sumber_data', [
                'UMUM',
                'BPJS',
                'JAMINAN',
                'KERJASAMA',
                'LAIN'
            ])->nullable()->change();
        });
    }
};
