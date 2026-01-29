<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('pendapatan_umum', function (Blueprint $table) {
        if (!Schema::hasColumn('pendapatan_umum', 'rs_tindakan')) {
            $table->unsignedBigInteger('rs_tindakan')->default(0);
            $table->unsignedBigInteger('rs_obat')->default(0);
            $table->unsignedBigInteger('pelayanan_tindakan')->default(0);
            $table->unsignedBigInteger('pelayanan_obat')->default(0);
            $table->unsignedBigInteger('total')->default(0);
        }
    });
}

public function down(): void
{
    Schema::table('pendapatan_umum', function (Blueprint $table) {
        $table->dropColumn([
            'rs_tindakan',
            'rs_obat',
            'pelayanan_tindakan',
            'pelayanan_obat',
            'total',
        ]);
    });
}

};
