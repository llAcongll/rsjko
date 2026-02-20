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
            $table->enum('metode_pembayaran', ['UP', 'GU', 'LS'])->nullable()->after('nominal');
            $table->string('no_spm')->nullable()->after('metode_pembayaran');
            $table->string('no_sp2d')->nullable()->after('no_spm');
            $table->string('no_spp')->nullable()->after('no_sp2d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->dropColumn(['metode_pembayaran', 'no_spm', 'no_sp2d', 'no_spp']);
        });
    }
};
