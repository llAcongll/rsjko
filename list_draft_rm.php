<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rms = \DB::table('revenue_masters')->where('is_posted', 0)->get();
foreach ($rms as $r) {
    echo "ID {$r->id} - Date: {$r->tanggal} - Total: {$r->total_all}\n";
}
