<?php

namespace App\Http\Controllers;

use App\Models\BkuPenerimaan;
use App\Services\IncomeCashBookService;
use Illuminate\Http\Request;

class BkuPenerimaanController extends Controller
{
    protected $service;

    public function __construct(IncomeCashBookService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('BKU_PENDAPATAN_VIEW'), 403);

        $year = $request->get('year', session('tahun_anggaran', date('Y')));
        $month = $request->get('month');

        $data = $this->service->getLedgerData((int) $year, $month ? (int) $month : null);

        return response()->json($data);
    }

    public function sync(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('BKU_PENDAPATAN_SYNC'), 403);
        $year = $request->get('year', session('tahun_anggaran', date('Y')));
        $this->service->syncLedger($year);
        return response()->json(['message' => 'BKU Pendapatan synchronized successfully']);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('BKU_PENDAPATAN_SYNC'), 403);
        $request->validate([
            'tanggal' => 'required|date',
            'uraian' => 'required|string',
            'penerimaan' => 'numeric|min:0',
            'pengeluaran' => 'numeric|min:0'
        ]);

        $this->service->recordEntry(
            $request->tanggal,
            $request->uraian,
            $request->penerimaan,
            $request->pengeluaran,
            'MANUAL',
            uniqid()
        );

        return response()->json(['message' => 'Entry saved successfully']);
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->hasPermission('BKU_PENDAPATAN_SYNC'), 403);
        $entry = BkuPenerimaan::findOrFail($id);
        $year = \Carbon\Carbon::parse($entry->tanggal)->year;
        $entry->delete();
        $this->service->rebuildBalances($year);

        return response()->json(['message' => 'Entry deleted successfully']);
    }

    public function exportPdf(\Illuminate\Http\Request $request)
    {
        abort_unless(auth()->user()->hasPermission('BKU_PENDAPATAN_EXPORT'), 403);
        $month = $request->get('month');
        $year = $request->get('year', session('tahun_anggaran', date('Y')));
        $data = $this->service->getLedgerData((int) $year, $month ? (int) $month : null);

        $period = $month ? \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') : "Tahun $year";

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.exports.bku_penerimaan_pdf', [
            'data' => $data['data'],
            'summary' => $data['summary'],
            'opening_balance' => $data['opening_balance'],
            'period' => $period,
            'ptKiri' => $request->has('pt_id_kiri') ? \App\Models\PenandaTangan::find($request->pt_id_kiri) : null,
            'ptTengah' => $request->has('pt_id_tengah') ? \App\Models\PenandaTangan::find($request->pt_id_tengah) : null,
            'ptKanan' => $request->has('pt_id_kanan') ? \App\Models\PenandaTangan::find($request->pt_id_kanan) : null,
        ]);

        return $pdf->download("BKU_Pendapatan_{$period}.pdf");
    }
}





