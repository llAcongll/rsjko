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
        Schema::create('penanda_tangans', function (Blueprint $table) {
            $table->id();
            $table->string('jabatan', 100);
            $table->string('pangkat', 100)->nullable();
            $table->string('nama', 100);
            $table->string('nip', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penanda_tangans');
    }
};
