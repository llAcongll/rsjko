<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('pendapatan_umum', function (Blueprint $table) {
        if (!Schema::hasColumn('pendapatan_umum', 'metode_detail')) {
            $table->string('metode_detail')->nullable()->after('bank_id');
        }
    });
}

public function down(): void
{
    Schema::table('pendapatan_umum', function (Blueprint $table) {
        $table->dropColumn('metode_detail');
    });
}

};
