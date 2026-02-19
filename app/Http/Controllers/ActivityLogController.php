<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ActivityLogController extends BaseController
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $query = ActivityLog::with('user')->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                    ->orWhere('action', 'like', '%' . $request->search . '%')
                    ->orWhere('module', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($qu) use ($request) {
                        $qu->where('username', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->module) {
            $query->where('module', $request->module);
        }

        $logs = $query->paginate(20);

        return response()->json($logs);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        ActivityLog::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function purge(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $days = $request->days ?? 30; // Default 30 hari
        $date = now()->subDays($days);

        $count = ActivityLog::where('created_at', '<', $date)->delete();

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$count} log yang lebih lama dari {$days} hari."
        ]);
    }
}
