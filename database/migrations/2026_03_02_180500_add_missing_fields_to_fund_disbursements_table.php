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
            if (!Schema::hasColumn('fund_disbursements', 'kode_rekening_id')) {
                $table->unsignedBigInteger('kode_rekening_id')->nullable()->after('expenditure_id');
                $table->foreign('kode_rekening_id')->references('id')->on('kode_rekening')->onDelete('set null');
            }
            if (!Schema::hasColumn('fund_disbursements', 'uraian')) {
                $table->string('uraian', 500)->nullable()->after('kode_rekening_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fund_disbursements', function (Blueprint $table) {
            if (Schema::hasColumn('fund_disbursements', 'kode_rekening_id')) {
                $table->dropForeign(['kode_rekening_id']);
                $table->dropColumn('kode_rekening_id');
            }
            if (Schema::hasColumn('fund_disbursements', 'uraian')) {
                $table->dropColumn('uraian');
            }
        });
    }
};





