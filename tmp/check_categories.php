<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$categories = DB::table('revenue_masters')->select('kategori', DB::raw('count(*) as count'))->groupBy('kategori')->get();
foreach ($categories as $c) {
    echo "Category: '{$c->kategori}', Count: {$c->count}\n";
}





