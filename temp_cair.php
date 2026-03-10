<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $d = App\Models\FundDisbursement::where('type', 'LS')->first();
    if ($d) {
        $service = app(App\Services\DisbursementService::class);
        $d->status = 'SPM';
        $d->save();
        $service->updateStatus($d->id, 'CAIR');

        $exp = new App\Models\Expenditure();
        $exp->spending_date = $d->sp2d_date;
        $exp->spending_type = 'LS';
        $exp->no_bukti = $d->sp2d_no;
        $exp->gross_value = $d->value;
        $exp->net_value = $d->value;
        $exp->description = 'Belanja LS';
        $exp->kode_rekening_id = $d->kode_rekening_id;
        $exp->created_by = 1;
        $exp->save();

        $cService = app(App\Services\CashLedgerService::class);
        $cService->syncLedger(2026);
        echo "Success";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}





