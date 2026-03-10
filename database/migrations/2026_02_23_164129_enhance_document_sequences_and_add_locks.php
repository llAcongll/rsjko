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
        Schema::table('document_sequences', function (Blueprint $table) {
            $table->integer('siklus_up')->nullable()->after('tahun');
            $table->string('sub_key')->nullable()->after('siklus_up'); // For 'jenis' like UP, GU, LS

            // Update unique key for multi-tenancy numbering
            $table->dropUnique(['type', 'tahun']);
            $table->index(['type', 'tahun', 'siklus_up', 'sub_key'], 'idx_doc_seq_full');
        });

        Schema::table('expenditures', function (Blueprint $table) {
            $table->timestamp('number_locked_at')->nullable()->after('no_bukti_urut');
        });

        Schema::table('fund_disbursements', function (Blueprint $table) {
            $table->timestamp('number_locked_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_sequences', function (Blueprint $table) {
            $table->dropIndex('idx_doc_seq_full');
            $table->unique(['type', 'tahun']);
            $table->dropColumn(['siklus_up', 'sub_key']);
        });

        Schema::table('expenditures', function (Blueprint $table) {
            $table->dropColumn('number_locked_at');
        });

        Schema::table('fund_disbursements', function (Blueprint $table) {
            $table->dropColumn('number_locked_at');
        });
    }
};





