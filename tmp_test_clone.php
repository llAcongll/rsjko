<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$query = \App\Models\RevenueMaster::where('kategori', 'UMUM')->where('total_all', '>=', 0);

$paginated = $query->paginate(2, ['*'], 'page', 2); // get page 2
$totalQuery = clone $query;

$totals = $totalQuery->reorder()->selectRaw('SUM(total_all) as grand_total')->first();

echo "Page 2 aggregate when cloned AFTER paginate: " . floatval($totals->grand_total) . "\n";





