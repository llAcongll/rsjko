<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$masters = DB::table('revenue_masters')->select('id', 'tanggal', 'tahun', 'kategori')->get();
echo "Total masters: " . $masters->count() . "\n";
foreach ($masters as $m) {
    echo "ID: {$m->id}, Tanggal: {$m->tanggal}, Tahun: {$m->tahun}, Kategori: {$m->kategori}\n";
}





