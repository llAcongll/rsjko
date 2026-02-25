<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expenditures', function (Blueprint $table) {
            $table->integer('nomor_dalam_siklus')->nullable()->after('siklus_up');
        });
    }

    public function down(): void
    {
        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropColumn('nomor_dalam_siklus');
        });
    }
};
