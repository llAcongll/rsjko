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
            $table->string('no_bukti')->nullable()->after('nomor_dalam_siklus');
            $table->integer('no_bukti_urut')->nullable()->after('no_bukti');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fund_disbursements', function (Blueprint $table) {
            $table->dropColumn(['no_bukti', 'no_bukti_urut']);
        });
    }
};
