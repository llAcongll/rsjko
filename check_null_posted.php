<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$nullCount = DB::table('revenue_masters')->whereNull('is_posted')->count();
echo "NULL is_posted count: $nullCount\n";
