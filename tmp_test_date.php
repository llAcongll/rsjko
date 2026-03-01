<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rm = App\Models\RevenueMaster::where('id', 119)->first();
echo json_encode($rm->toArray()) . "\n";
