<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FundDisbursement;
try {
    $sum = FundDisbursement::where('tahun', 2026)
        ->where('type', 'UP')
        ->where('status', 'CAIR')
        ->where(function ($q) {
            $q->whereNull('spp_no')->orWhereNull('kode_rekening_id');
        })
        ->sum('value');
    echo "Sum: " . $sum . PHP_EOL;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Query: " . FundDisbursement::where('tahun', 2026)
        ->where('type', 'UP')
        ->where('status', 'CAIR')
        ->where(function ($q) {
            $q->whereNull('spp_no')->orWhereNull('kode_rekening_id');
        })->toSql() . PHP_EOL;
}
