<?php
$id = \App\Models\RevenueMaster::where('is_posted', true)->first()->id ?? null;
if ($id) {
    \App\Http\Controllers\RevenueMasterController::recalculate($id);
    dump(\App\Models\RekeningKoran::where('revenue_master_id', $id)->count());
} else {
    echo "No posted master found.\n";
}
