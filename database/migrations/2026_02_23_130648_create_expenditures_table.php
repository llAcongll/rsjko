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
        Schema::create('expenditures', function (Blueprint $table) {
            $table->id();
            $table->date('spending_date');
            $table->foreignId('kode_rekening_id')->constrained('kode_rekening')->onDelete('cascade');
            $table->string('description');
            $table->decimal('gross_value', 15, 2);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('net_value', 15, 2);
            $table->enum('spending_type', ['UP', 'LS']);
            $table->string('vendor')->nullable();
            $table->string('proof_number')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenditures');
    }
};





