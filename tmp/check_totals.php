<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$mastersWithZero = DB::table('revenue_masters')
    ->where('total_all', 0)
    ->count();

$totalMasters = DB::table('revenue_masters')->count();

echo "Total Masters: $totalMasters\n";
echo "Masters with Total=0: $mastersWithZero\n";

if ($totalMasters > 0) {
    $sample = DB::table('revenue_masters')->orderBy('id', 'desc')->limit(5)->get();
    foreach ($sample as $s) {
        echo "ID: {$s->id}, Kategori: {$s->kategori}, Total: {$s->total_all}\n";
    }
}
