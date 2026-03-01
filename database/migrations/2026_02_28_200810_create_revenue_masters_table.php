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
        Schema::create('revenue_masters', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->integer('tahun');
            $table->string('kategori'); // UMUM, BPJS, JAMINAN, LAIN, KERJASAMA
            $table->string('no_bukti')->nullable();
            $table->text('keterangan')->nullable();

            // Total agregat dari child
            $table->decimal('total_rs', 15, 2)->default(0);
            $table->decimal('total_pelayanan', 15, 2)->default(0);
            $table->decimal('total_all', 15, 2)->default(0);

            $table->string('status')->default('DRAFT'); // DRAFT, FIX (jika sudah masuk rekon/bku dll)

            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index(['kategori', 'tahun', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_masters');
    }
};
