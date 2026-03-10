<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$t = 'sp3bp_pendapatans';
echo "--- $t ---\n";
if (Schema::hasTable($t)) {
    $years = DB::table($t)->select('tahun', DB::raw('count(*) as count'))->groupBy('tahun')->get();
    foreach ($years as $y) {
        echo "Year: {$y->tahun}, Total: {$y->count}\n";
    }
} else {
    echo "Table not found\n";
}





