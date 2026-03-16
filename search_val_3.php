<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$target = 10661;
$target2 = 3814;
$tables = ['rekening_korans', 'bank_account_ledgers', 'treasurer_cash', 'bku_penerimaan', 'pendapatan_umum', 'revenue_masters', 'penyesuaian_pendapatans', 'expenditures'];

foreach ($tables as $t) {
    try {
        $cols = \Schema::getColumnListing($t);
        foreach ($cols as $c) {
            $cnt = \DB::table($t)->where($c, $target)->count();
            if ($cnt > 0)
                echo "Found $cnt records (10661) in $t ($c)\n";
            $cnt2 = \DB::table($t)->where($c, $target2)->count();
            if ($cnt2 > 0)
                echo "Found $cnt2 records (3814) in $t ($c)\n";
        }
    } catch (\Exception $e) {
    }
}
