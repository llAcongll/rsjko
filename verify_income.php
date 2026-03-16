<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = '2026-01-01';
$end = '2026-01-31';
$year = 2026;

$tables = ['pendapatan_umum', 'pendapatan_bpjs', 'pendapatan_jaminan', 'pendapatan_kerjasama', 'pendapatan_lain'];
$rawSum = 0;
foreach ($tables as $tbl) {
    $rawSum += \DB::table("$tbl as t")
        ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
        ->whereBetween('t.tanggal', [$start, $end])
        ->where('rm.tahun', $year)
        ->where('rm.is_posted', 1)
        ->sum('t.total');
}

$penyesuaian = \DB::table('penyesuaian_pendapatans')
    ->whereBetween('tanggal', [$start, $end])
    ->where('tahun', $year)
    ->sum(\DB::raw('IFNULL(potongan, 0) + IFNULL(administrasi_bank, 0)'));

echo "Raw Sum: $rawSum\n";
echo "Penyesuaian: $penyesuaian\n";
echo "Net: " . ($rawSum - $penyesuaian) . "\n";
