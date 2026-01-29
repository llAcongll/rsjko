<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pendapatan_umum', function (Blueprint $table) {

            $table->date('tanggal')->after('id');
            $table->string('nama_pasien')->after('tanggal');

            $table->foreignId('ruangan_id')
                ->after('nama_pasien')
                ->constrained('ruangans')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('metode_pembayaran', ['TUNAI', 'NON_TUNAI'])
                ->after('ruangan_id');

            $table->foreignId('bank_id')
                ->nullable()
                ->after('metode_pembayaran')
                ->constrained('banks')
                ->nullOnDelete();

            $table->string('metode_detail')
                ->nullable()
                ->after('bank_id');

            // ===== NOMINAL =====
            $table->unsignedBigInteger('rs_tindakan')->default(0);
            $table->unsignedBigInteger('rs_obat')->default(0);
            $table->unsignedBigInteger('pelayanan_tindakan')->default(0);
            $table->unsignedBigInteger('pelayanan_obat')->default(0);

            $table->unsignedBigInteger('total')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('pendapatan_umum', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal',
                'nama_pasien',
                'ruangan_id',
                'metode_pembayaran',
                'bank_id',
                'metode_detail',
                'rs_tindakan',
                'rs_obat',
                'pelayanan_tindakan',
                'pelayanan_obat',
                'total',
            ]);
        });
    }
};
