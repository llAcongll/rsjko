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
        Schema::create('fund_disbursements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['UP', 'GU', 'LS']);
            $table->date('sp2d_date')->nullable();
            $table->string('sp2d_no')->nullable();
            $table->string('spm_no')->nullable();
            $table->string('spp_no')->nullable();
            $table->decimal('value', 15, 2);
            $table->string('recipient_party')->nullable();
            $table->string('destination_account')->nullable();
            $table->foreignId('spj_id')->nullable()->constrained('spj')->onDelete('set null');
            $table->foreignId('expenditure_id')->nullable()->constrained('expenditures')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_disbursements');
    }
};
