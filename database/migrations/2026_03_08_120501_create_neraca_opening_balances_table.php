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
        Schema::create('neraca_opening_balances', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->string('kelompok'); // ASET_LANCAR, ASET_TETAP, KEWAJIBAN, EKUITAS
            $table->string('sub_kelompok'); // KAS, PIUTANG, PERSEDIAAN, TANAH, GEDUNG, PIUTANG_TAHUN_LALU, etc.
            $table->decimal('nominal', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neraca_opening_balances');
    }
};





