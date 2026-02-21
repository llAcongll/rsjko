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
            $table->decimal('potongan_pajak', 15, 2)->default(0)->after('nominal');
            $table->decimal('total_dibayarkan', 15, 2)->default(0)->after('potongan_pajak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->dropColumn(['potongan_pajak', 'total_dibayarkan']);
        });
    }
};
