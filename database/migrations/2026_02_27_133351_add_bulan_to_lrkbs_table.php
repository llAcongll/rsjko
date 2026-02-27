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
        Schema::table('lrkbs', function (Blueprint $table) {
            $table->tinyInteger('bulan')->nullable()->after('tahun');
            $table->tinyInteger('triwulan')->nullable()->change();

            // Drop old unique
            $table->dropUnique(['tahun', 'triwulan']);
            // Add new unique including bulan
            $table->unique(['tahun', 'triwulan', 'bulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lrkbs', function (Blueprint $table) {
            $table->dropUnique(['tahun', 'triwulan', 'bulan']);
            $table->dropColumn('bulan');
            $table->tinyInteger('triwulan')->change();
            $table->unique(['tahun', 'triwulan']);
        });
    }
};
