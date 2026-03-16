<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$year = 2026;
$start = '2026-01-01';
$end = '2026-01-31';

echo "--- COMPARING REVENUE MASTER VS REKENING KORAN ---\n";

$rms = \DB::table('revenue_masters')
    ->where('tahun', $year)
    ->whereBetween('tanggal', [$start, $end])
    ->get();

$totalRm = 0;
$totalRk = 0;
$diffs = [];

foreach ($rms as $rm) {
    $totalRm += $rm->total_all;

    // Sum RK for this RM
    $rkSum = \DB::table('rekening_korans')
        ->where('revenue_master_id', $rm->id)
        ->sum('jumlah');

    $totalRk += $rkSum;

    if (abs($rm->total_all - $rkSum) > 0.01 && $rm->metode_pembayaran != 'TUNAI') {
        $diffs[] = [
            'id' => $rm->id,
            'no_bukti' => $rm->no_bukti,
            'total_all' => $rm->total_all,
            'rk_sum' => $rkSum,
            'diff' => $rm->total_all - $rkSum
        ];
    }
}

echo "Total RM.total_all: $totalRm\n";
echo "Total RK mapped to RM: $totalRk\n";
echo "Difference: " . ($totalRm - $totalRk) . "\n";

if (!empty($diffs)) {
    echo "\nMismatched non-cash items:\n";
    foreach ($diffs as $d) {
        echo "ID {$d['id']} ({$d['no_bukti']}): RM={$d['total_all']}, RK={$d['rk_sum']}, Diff={$d['diff']}\n";
    }
}

// Check Penyesuaian again
$adj = \DB::table('penyesuaian_pendapatans')
    ->whereYear('tanggal', $year)
    ->whereMonth('tanggal', 1)
    ->get();

echo "\n--- PENYESUAIAN PENDAPATAN JAN 2026 ---\n";
foreach ($adj as $a) {
    echo "ID {$a->id}: Potongan={$a->potongan}, Admin={$a->administrasi_bank}, Total=" . ($a->potongan + $a->administrasi_bank) . "\n";
}
