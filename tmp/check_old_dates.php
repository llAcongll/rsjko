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
    $oldData = DB::table($t)->whereRaw('YEAR(tanggal) < 2026')->count();
    echo "$t: pre-2026 records=$oldData\n";
    if ($oldData > 0) {
        $sample = DB::table($t)->whereRaw('YEAR(tanggal) < 2026')->limit(5)->get();
        foreach ($sample as $s) {
            echo "   ID: {$s->id}, Tanggal: {$s->tanggal}, Tahun: {$s->tahun}\n";
        }
    }
}





