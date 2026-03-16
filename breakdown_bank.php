<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$year = 2026;
$end = '2026-01-31';

echo "--- INCOME BANK (RK) BREAKDOWN ---\n";
$rk = \DB::table('rekening_korans')
    ->where('tahun', $year)
    ->where('tanggal', '<=', $end)
    ->select(
        'bank',
        \DB::raw('SUM(CASE WHEN cd = "C" THEN jumlah ELSE 0 END) as total_c'),
        \DB::raw('SUM(CASE WHEN cd = "D" THEN jumlah ELSE 0 END) as total_d')
    )
    ->groupBy('bank')
    ->get();

foreach ($rk as $r) {
    echo "{$r->bank}: C={$r->total_c}, D={$r->total_d}, Bal=" . ($r->total_c - $r->total_d) . "\n";
}

echo "\n--- EXPENDITURE BANK (LEDGER) BREAKDOWN ---\n";
$ledger = \DB::table('bank_account_ledgers')
    ->where('date', '<=', $end)
    ->select(
        'bank',
        \DB::raw('SUM(debit) as total_debit'),
        \DB::raw('SUM(credit) as total_credit')
    )
    ->groupBy('bank')
    ->get();

foreach ($ledger as $l) {
    echo "{$l->bank}: Debit={$l->total_debit}, Credit={$l->total_credit}, Bal=" . ($l->total_debit - $l->total_credit) . "\n";
}
