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
        Schema::table('bank_account_ledgers', function (Blueprint $table) {
            $table->string('bank')->default('BRK')->after('type');
        });

        Schema::table('rekening_korans', function (Blueprint $table) {
            $table->string('destination_bank')->nullable()->after('bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_account_ledgers', function (Blueprint $table) {
            $table->dropColumn('bank');
        });

        Schema::table('rekening_korans', function (Blueprint $table) {
            $table->dropColumn('destination_bank');
        });
    }
};
