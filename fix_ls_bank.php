<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FundDisbursement;
use App\Services\BankLedgerService;

$items = FundDisbursement::where('status', 'CAIR')->where('type', 'LS')->get();
$svc = app(BankLedgerService::class);

foreach ($items as $item) {
    echo "Processing LS: " . $item->sp2d_no . "\n";
    $svc->recordEntry(
        $item->sp2d_date,
        'WITHDRAW_LS',
        $item->value,
        'fund_disbursements',
        $item->id,
        'CREDIT',
        "Penarikan SP2D LS " . $item->sp2d_no
    );
}
echo "Done.\n";
