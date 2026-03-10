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
    if (!Schema::hasTable($t))
        continue;
    $count = DB::table($t)->whereNull('revenue_master_id')->count();
    echo "$t: $count orphaned records\n";
    if ($count > 0) {
        $dates = DB::table($t)->whereNull('revenue_master_id')->distinct()->pluck('tanggal');
        echo "   Dates: " . $dates->implode(', ') . "\n";
    }
}





