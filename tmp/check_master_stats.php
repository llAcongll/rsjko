<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$stats = DB::table('revenue_masters')->select(
    DB::raw('MIN(id) as min_id'),
    DB::raw('MAX(id) as max_id'),
    DB::raw('MIN(created_at) as min_MANAGEd'),
    DB::raw('MAX(created_at) as max_MANAGEd')
)->first();

echo "Min ID: {$stats->min_id}, Max ID: {$stats->max_id}\n";
echo "Min Created: {$stats->min_MANAGEd}, Max Created: {$stats->max_MANAGEd}\n";





