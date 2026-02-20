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
            $table->integer('no_spm_index')->nullable()->after('no_spp_metode_index');
            $table->integer('no_spm_metode_index')->nullable()->after('no_spm_index');
            $table->integer('no_sp2d_index')->nullable()->after('no_spm_metode_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->dropColumn(['no_spm_index', 'no_spm_metode_index', 'no_sp2d_index']);
        });
    }
};
