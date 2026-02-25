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
        Schema::table('fund_disbursements', function (Blueprint $table) {
            $table->integer('tahun')->after('id');
            $table->integer('nomor_paket')->after('tahun');
            $table->integer('siklus_up')->after('nomor_paket')->nullable();
            $table->integer('nomor_dalam_siklus')->after('siklus_up')->nullable();
            $table->enum('status', ['DRAFT', 'SPP', 'SPM', 'CAIR'])->default('DRAFT')->after('expenditure_id');

            // Allow nulls for old record transition if any, though it's likely fresh
            $table->index(['tahun', 'nomor_paket']);
            $table->index(['tahun', 'siklus_up', 'nomor_dalam_siklus']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fund_disbursements', function (Blueprint $table) {
            $table->dropColumn(['tahun', 'nomor_paket', 'siklus_up', 'nomor_dalam_siklus', 'status']);
        });
    }
};
