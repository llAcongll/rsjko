<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$year = 2026;
$end = '2026-01-31';

echo "--- BANK BALANCE END OF JAN 2026 ---\n";
$rk = \DB::table('rekening_korans')
    ->where('tahun', $year)
    ->where('tanggal', '<=', $end)
    ->select('bank', \DB::raw('SUM(CASE WHEN cd = "C" THEN jumlah ELSE -jumlah END) as balance'))
    ->groupBy('bank')
    ->get();

foreach ($rk as $r) {
    echo "Income Bank ({$r->bank}): {$r->balance}\n";
}

$ledger = \DB::table('bank_account_ledgers')
    ->where('date', '<=', $end)
    ->select('bank', \DB::raw('SUM(debit - credit) as balance'))
    ->groupBy('bank')
    ->get();

foreach ($ledger as $l) {
    echo "Expenditure Ledger ({$l->bank}): {$l->balance}\n";
}
