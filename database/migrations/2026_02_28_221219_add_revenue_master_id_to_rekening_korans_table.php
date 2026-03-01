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
        Schema::table('rekening_korans', function (Blueprint $table) {
            $table->unsignedBigInteger('revenue_master_id')->nullable()->after('id');
            $table->foreign('revenue_master_id')->references('id')->on('revenue_masters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekening_korans', function (Blueprint $table) {
            $table->dropForeign(['revenue_master_id']);
            $table->dropColumn('revenue_master_id');
        });
    }
};
