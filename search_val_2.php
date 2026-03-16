<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$target = 1550332;
$tables = ['rekening_korans', 'bank_account_ledgers', 'treasurer_cash', 'bku_penerimaan', 'pendapatan_umum'];

foreach ($tables as $t) {
    echo "Searching in $t...\n";
    // Check various columns
    $cols = \Schema::getColumnListing($t);
    foreach ($cols as $col) {
        try {
            $found = \DB::table($t)->where($col, $target)->get();
            if ($found->count() > 0)
                echo "Found in $t ($col): " . json_encode($found) . "\n";
        } catch (\Exception $e) {
        }
    }
}
