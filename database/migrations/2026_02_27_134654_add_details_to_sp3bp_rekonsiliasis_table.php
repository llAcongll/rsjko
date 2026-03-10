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
        Schema::table('sp3bp_rekonsiliasis', function (Blueprint $table) {
            $table->decimal('bank_masuk', 18, 2)->default(0)->after('sp3bp_id');
            $table->decimal('bank_keluar', 18, 2)->default(0)->after('bank_masuk');
            $table->decimal('tunai_masuk', 18, 2)->default(0)->after('saldo_bank');
            $table->decimal('tunai_keluar', 18, 2)->default(0)->after('tunai_masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sp3bp_rekonsiliasis', function (Blueprint $table) {
            $table->dropColumn(['bank_masuk', 'bank_keluar', 'tunai_masuk', 'tunai_keluar']);
        });
    }
};





