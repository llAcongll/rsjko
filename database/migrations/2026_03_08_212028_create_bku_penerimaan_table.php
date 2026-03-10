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
        Schema::create('bku_penerimaan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('uraian', 255);
            $table->decimal('penerimaan', 16, 2)->default(0);
            $table->decimal('pengeluaran', 16, 2)->default(0);
            $table->decimal('saldo', 18, 2)->default(0);
            $table->string('sumber', 50)->nullable();
            $table->string('reference_id', 50)->nullable();
            $table->timestamps();

            $table->index(['tanggal', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bku_penerimaan');
    }
};





