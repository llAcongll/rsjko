<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekening_korans', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal');
            $table->string('bank', 50);
            $table->string('keterangan', 255);

            // C = Credit, D = Debit
            $table->enum('cd', ['C', 'D'])->default('C');

            $table->bigInteger('jumlah');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekening_korans');
    }
};
