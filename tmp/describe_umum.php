<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select('DESCRIBE pendapatan_umum');
foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type})\n";
}
