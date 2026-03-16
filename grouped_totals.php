<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = '2026-01-01';
$end = '2026-01-31';

echo "--- BKU PENERIMAAN GROUP BY CATEGORY ---\n";
$res = \DB::table('bku_penerimaan')
    ->whereBetween('tanggal', [$start, $end])
    ->select('category', \DB::raw('SUM(penerimaan) as total_in'), \DB::raw('SUM(pengeluaran) as total_out'))
    ->groupBy('category')
    ->get();

foreach ($res as $r) {
    echo "{$r->category}: In={$r->total_in}, Out={$r->total_out}, Net=" . ($r->total_in - $r->total_out) . "\n";
}

echo "\n--- TREASURER CASH GROUP BY TYPE ---\n";
$res2 = \DB::table('treasurer_cash')
    ->whereBetween('date', [$start, $end])
    ->select('type', \DB::raw('SUM(debit) as total_debit'), \DB::raw('SUM(credit) as total_credit'))
    ->groupBy('type')
    ->get();

foreach ($res2 as $r) {
    echo "{$r->type}: Debit={$r->total_debit}, Credit={$r->total_credit}\n";
}
