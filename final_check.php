<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$year = 2026;
$month = 1;

$endDate = '2026-01-31';

$rkSum = \DB::table('rekening_korans')
    ->where('tahun', $year)
    ->where('tanggal', '<=', $endDate)
    ->select('bank', \DB::raw('SUM(CASE WHEN cd = "C" THEN jumlah ELSE -jumlah END) as balance'))
    ->groupBy('bank')
    ->get();

foreach ($rkSum as $r) {
    echo "{$r->bank} RK SUM: {$r->balance}\n";
}

$ledgerSum = \DB::table('bank_account_ledgers')
    ->whereYear('date', $year)
    ->where('date', '<=', $endDate)
    ->select('bank', \DB::raw('SUM(debit - credit) as balance'))
    ->groupBy('bank')
    ->get();

foreach ($ledgerSum as $l) {
    echo "{$l->bank} LEDGER SUM: {$l->balance}\n";
}

$tunaiSum = \DB::table('bku_penerimaan')
    ->whereYear('tanggal', $year)
    ->where('tanggal', '<=', $endDate)
    ->orderBy('tanggal', 'desc')
    ->orderBy('id', 'desc')
    ->value('saldo');
echo "BKU Tunai Saldo: $tunaiSum\n";
