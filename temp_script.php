<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$d = App\Models\FundDisbursement::where('type', 'LS')->first();
if ($d) {
    app(App\Services\BankLedgerService::class)->recordEntry($d->sp2d_date, 'DEPOSIT_LS', $d->value, 'fund_disbursements', $d->id, 'DEBIT', 'Penerimaan SP2D LS ' . $d->sp2d_no);
    echo "Success deposits LS into bank \n";
}
