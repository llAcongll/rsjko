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
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->integer('no_spp_index')->nullable()->after('no_spp');
            $table->integer('no_spp_metode_index')->nullable()->after('no_spp_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->dropColumn(['no_spp_index', 'no_spp_metode_index']);
        });
    }
};
