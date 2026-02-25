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
        Schema::create('bank_account_ledgers', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('type')->comment('DEPOSIT, WITHDRAW_UP, WITHDRAW_GU, WITHDRAW_LS');
            $table->decimal('debit', 15, 2)->default(0)->comment('Masuk Rekening');
            $table->decimal('credit', 15, 2)->default(0)->comment('Keluar Rekening');
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('description')->nullable();

            // Reference to SP2D or Saldo Dana addition
            $table->string('ref_table')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_account_ledgers');
    }
};
