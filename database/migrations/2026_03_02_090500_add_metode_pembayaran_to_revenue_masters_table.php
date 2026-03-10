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
        Schema::table('revenue_masters', function (Blueprint $table) {
            if (!Schema::hasColumn('revenue_masters', 'metode_pembayaran')) {
                $table->string('metode_pembayaran', 50)->nullable()->after('kategori');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revenue_masters', function (Blueprint $table) {
            $table->dropColumn('metode_pembayaran');
        });
    }
};





