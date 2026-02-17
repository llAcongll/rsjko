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
            $table->string('sub_kategori')->nullable()->after('kategori')->comment('Untuk BPJS: REGULAR, EVAKUASI, OBAT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyesuaian_pendapatans', function (Blueprint $table) {
            $table->dropColumn('sub_kategori');
        });
    }
};
