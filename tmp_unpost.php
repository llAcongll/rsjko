<?php
$id = \App\Models\RevenueMaster::where('is_posted', true)->first()->id ?? null;
if ($id) {
    echo "Before: " . \App\Models\RekeningKoran::where('revenue_master_id', $id)->count() . "\n";
    $master = \App\Models\RevenueMaster::find($id);
    $master->is_posted = false;
    $master->save();
    \App\Http\Controllers\RevenueMasterController::recalculate($id);
    echo "After toggle false: " . \App\Models\RekeningKoran::where('revenue_master_id', $id)->count() . "\n";

    // Restore
    $master->is_posted = true;
    $master->save();
    \App\Http\Controllers\RevenueMasterController::recalculate($id);
    echo "After restore to true: " . \App\Models\RekeningKoran::where('revenue_master_id', $id)->count() . "\n";
}





