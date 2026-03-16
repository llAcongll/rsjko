<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$year = 2026;
$m = 1;

$r = app(\App\Services\ReportService::class);
$i = app(\App\Services\IncomeCashBookService::class);

$bkuExp = $r->getBkuData($year, $m);
$bkuInc = $i->getLedgerData($year, $m);

$fbe = $bkuExp['summary']['final_bank'] ?? 0;
$fte = $bkuExp['summary']['final_tunai'] ?? 0;
$fbi = ($bkuInc['summary']['bank_brk'] ?? 0) + ($bkuInc['summary']['bank_bsi'] ?? 0);
$fti = $bkuInc['summary']['final_saldo'] ?? 0;

echo "Final Bank Exp: $fbe\n";
echo "Final Tunai Exp: $fte\n";
echo "Final Bank Inc: $fbi\n";
echo "Final Tunai Inc: $fti\n";
echo "TOTAL FISIK: " . ($fbe + $fte + $fbi + $fti) . "\n";
