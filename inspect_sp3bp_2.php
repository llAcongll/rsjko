<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$start = '2026-01-01';
$end = '2026-01-31';

$sum = DB::table('pendapatan_lain as t')
    ->join('revenue_masters as rm', 't.revenue_master_id', '=', 'rm.id')
    ->where('rm.tahun', 2026)
    ->whereIn('rm.is_posted', [0, 1])
    ->whereBetween('t.tanggal', [$start, $end])
    ->sum('t.total');

echo "LAIN: $sum\n";

$all = DB::table('penyesuaian_pendapatans')
    ->whereBetween('tanggal', [$start, $end])
    ->where('tahun', 2026)
    ->get();

print_r($all);
