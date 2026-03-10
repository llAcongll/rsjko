<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (\App\Models\Expenditure::all() as $e) {
    if ($e->spending_type === 'LS') {
        app(\App\Services\BankLedgerService::class)->recordEntry($e->spending_date, 'WITHDRAW_LS', $e->gross_value, 'expenditures', $e->id, 'CREDIT', $e->no_bukti . ' - Belanja LS');
    }
}
echo "Done";





