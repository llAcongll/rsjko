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
        Schema::table('treasurer_cash', function (Blueprint $table) {
            // Changing from ENUM to STRING for better flexibility as the system evolves
            $table->string('type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treasurer_cash', function (Blueprint $table) {
            $table->enum('type', ['TERIMA_UP', 'BELANJA_UP', 'GU'])->change();
        });
    }
};
