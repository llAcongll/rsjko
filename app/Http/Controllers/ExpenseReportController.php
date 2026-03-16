<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;
use Carbon\Carbon;

class ExpenseReportController extends Controller
{
    protected $service;

    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorizePermission('LAP_PENGELUARAN_VIEW');
        $start = $request->get('start', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->get('end', Carbon::now()->toDateString());
        $tahun = session('tahun_anggaran');

        return response()->json($this->service->getPengeluaranSummary($start, $end, $tahun));
    }
}
