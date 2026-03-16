<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'rekening_korans' => 'jumlah',
    'bank_account_ledgers' => ['debit', 'credit'],
    'treasurer_cash' => ['debit', 'credit'],
    'bku_penerimaan' => ['penerimaan', 'pengeluaran'],
    'pendapatan_umum' => 'total',
    'revenue_masters' => 'total_amount'
];
$target = 193028765;
$target2 = 314250;

echo "Searching for $target...\n";
foreach ($tables as $t => $cols) {
    if (!is_array($cols))
        $cols = [$cols];
    foreach ($cols as $col) {
        $found = \DB::table($t)->where($col, $target)->get();
        if ($found->count() > 0)
            echo "Found in $t ($col): " . json_encode($found) . "\n";
    }
}

echo "\nSearching for $target2...\n";
foreach ($tables as $t => $cols) {
    if (!is_array($cols))
        $cols = [$cols];
    foreach ($cols as $col) {
        $found = \DB::table($t)->where($col, $target2)->get();
        if ($found->count() > 0)
            echo "Found in $t ($col): " . json_encode($found) . "\n";
    }
}
