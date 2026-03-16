<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$year = 2026;
$m = 1;

$i = app(\App\Services\IncomeCashBookService::class);
$bkuInc = $i->getLedgerData($year, $m);

echo "BSI: " . $bkuInc['summary']['bank_bsi'] . "\n";
echo "BRK: " . $bkuInc['summary']['bank_brk'] . "\n";
echo "Tunai: " . $bkuInc['summary']['final_saldo'] . "\n";
