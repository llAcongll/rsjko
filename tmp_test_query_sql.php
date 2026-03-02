<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FundDisbursement;
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();
FundDisbursement::where('tahun', 2026)
    ->where('type', 'UP')
    ->where('status', 'CAIR')
    ->where(function ($q) {
        $q->whereNull('spp_no')->orWhereNull('kode_rekening_id');
    })
    ->sum('value');

print_r(DB::getQueryLog());
