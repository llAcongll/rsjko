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
        Schema::table('penyesuaian_pendapatans', function (Blueprint $table) {
            $table->integer('tahun_piutang')->nullable()->after('tahun')->comment('Tahun tagihan piutang yang dikurangi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyesuaian_pendapatans', function (Blueprint $table) {
            $table->dropColumn('tahun_piutang');
        });
    }
};
