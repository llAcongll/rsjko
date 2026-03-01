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
        $tables = [
            'pendapatan_umum',
            'pendapatan_bpjs',
            'pendapatan_jaminan',
            'pendapatan_lain',
            'pendapatan_kerjasama'
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) use ($t) {
                $table->unsignedBigInteger('revenue_master_id')->nullable()->after('id');
                $table->foreign('revenue_master_id', "fk_{$t}_rm_id") // Custom short name for constraint
                    ->references('id')
                    ->on('revenue_masters')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'pendapatan_umum',
            'pendapatan_bpjs',
            'pendapatan_jaminan',
            'pendapatan_lain',
            'pendapatan_kerjasama'
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) use ($t) {
                $table->dropForeign("fk_{$t}_rm_id");
                $table->dropColumn('revenue_master_id');
            });
        }
    }
};
