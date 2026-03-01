<?php
$m = \App\Models\RevenueMaster::where('kategori', 'UMUM')->where('is_posted', true)->first();
if ($m) {
    echo "Before toggle: " . \App\Models\RekeningKoran::where('revenue_master_id', $m->id)->count() . "\n";
    $m->is_posted = false;
    $m->save();
    \App\Http\Controllers\RevenueMasterController::recalculate($m->id);
    echo "After toggle false: " . \App\Models\RekeningKoran::where('revenue_master_id', $m->id)->count() . "\n";
} else {
    echo "No posted data.\n";
}
