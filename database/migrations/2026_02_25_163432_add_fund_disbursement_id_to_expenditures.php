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
        Schema::table('expenditures', function (Blueprint $table) {
            $table->unsignedBigInteger('fund_disbursement_id')->nullable()->after('id');
            $table->foreign('fund_disbursement_id')->references('id')->on('fund_disbursements')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropForeign(['fund_disbursement_id']);
            $table->dropColumn('fund_disbursement_id');
        });
    }
};





