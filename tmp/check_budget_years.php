<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$years = DB::table('anggaran_rekening')->select('tahun', DB::raw('count(*) as count'))->groupBy('tahun')->get();
foreach ($years as $y) {
    echo "Year: {$y->tahun}, Total: {$y->count}\n";
}
