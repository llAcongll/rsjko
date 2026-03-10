<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fund_disbursements', function (Blueprint $row) {
            $row->integer('spp_urut')->nullable()->after('spp_no');
            $row->integer('spm_urut')->nullable()->after('spm_no');
            $row->integer('sp2d_urut')->nullable()->after('sp2d_no');
        });
    }

    public function down(): void
    {
        Schema::table('fund_disbursements', function (Blueprint $row) {
            $row->dropColumn(['spp_urut', 'spm_urut', 'sp2d_urut']);
        });
    }
};





