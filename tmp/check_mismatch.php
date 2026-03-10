<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$mismatches = DB::table('revenue_masters')
    ->whereRaw('tahun <> YEAR(tanggal)')
    ->count();

echo "Mismatched Years in RevenueMaster: $mismatches\n";
if ($mismatches > 0) {
    $sample = DB::table('revenue_masters')->whereRaw('tahun <> YEAR(tanggal)')->limit(5)->get();
    foreach ($sample as $s) {
        echo "   ID: {$s->id}, Tanggal: {$s->tanggal}, Tahun: {$s->tahun}\n";
    }
}





