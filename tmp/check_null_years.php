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
    $nullYears = DB::table($t)->whereNull('tahun')->count();
    $zeroYears = DB::table($t)->where('tahun', 0)->count();
    echo "$t: NULL years=$nullYears, 0 years=$zeroYears\n";
    if ($nullYears > 0 || $zeroYears > 0) {
        $sample = DB::table($t)->whereNull('tahun')->orWhere('tahun', 0)->limit(5)->get();
        foreach ($sample as $s) {
            echo "   ID: {$s->id}, Tanggal: " . ($s->tanggal ?? 'N/A') . "\n";
        }
    }
}





