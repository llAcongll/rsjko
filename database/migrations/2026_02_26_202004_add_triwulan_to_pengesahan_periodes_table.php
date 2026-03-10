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
        Schema::table('pengesahan_periodes', function (Blueprint $table) {
            $table->tinyInteger('triwulan')->nullable()->after('bulan');
            $table->tinyInteger('bulan')->nullable()->change();

            $table->dropUnique(['bulan', 'tahun']);
            $table->unique(['triwulan', 'tahun', 'bulan']); // Allow hybrid or purely triwulan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengesahan_periodes', function (Blueprint $table) {
            $table->dropUnique(['triwulan', 'tahun', 'bulan']);
            $table->unique(['bulan', 'tahun']);
            $table->dropColumn('triwulan');
            $table->tinyInteger('bulan')->change();
        });
    }
};





