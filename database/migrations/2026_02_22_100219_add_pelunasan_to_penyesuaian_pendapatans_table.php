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
        Schema::table('penyesuaian_pendapatans', function (Blueprint $table) {
            $table->decimal('pelunasan', 15, 2)->default(0)->after('perusahaan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyesuaian_pendapatans', function (Blueprint $table) {
            $table->dropColumn('pelunasan');
        });
    }
};
