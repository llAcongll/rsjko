<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$tables = ['expenditures', 'bku_penerimaan', 'bank_account_ledgers', 'treasurer_cash', 'revenue_masters', 'penyesuaian_pendapatans'];
foreach($tables as $t){
    $cols = Schema::getColumnListing($t);
    foreach($cols as $c){
        $res = DB::table($t)->where($c, 314250)->get();
        if($res->count() > 0){
            echo "Table: $t, Column: $c FOUND!\n";
            print_r($res);
        }
    }
}
