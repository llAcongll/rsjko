<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$counts = DB::table('revenue_masters')
    ->select('is_posted', DB::raw('count(*) as count'))
    ->groupBy('is_posted')
    ->get();

foreach ($counts as $c) {
    echo "Is Posted: " . ($c->is_posted ? 'TRUE' : 'FALSE') . ", Count: " . $c->count . "\n";
}
