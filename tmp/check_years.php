<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = [
    'pendapatan_umum',
    'pendapatan_bpjs',
    'pendapatan_jaminan',
    'pendapatan_lain',
    'pendapatan_kerjasama'
];

foreach ($tables as $t) {
    echo "--- $t ---\n";
    $years = DB::table($t)->select('tahun', DB::raw('count(*) as count'))->groupBy('tahun')->get();
    foreach ($years as $y) {
        $orphans = DB::table($t)->where('tahun', $y->tahun)->whereNull('revenue_master_id')->count();
        echo "Year: {$y->tahun}, Total: {$y->count}, Orphans: $orphans\n";
    }
}
