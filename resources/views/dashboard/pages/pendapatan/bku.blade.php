@php
    $title = 'Buku Kas Umum Pendapatan (BKU)';
@endphp

<div class="page-container">
    {{-- HEADER --}}
    <div class="page-header">
        <div class="page-header-left">
            <h2><i class="ph ph-trend-up"></i> {{ $title }}</h2>
            <p>Penerimaan Tunai Bendahara & Setoran ke Bank</p>
        </div>

        <div class="page-header-right">
            <button id="btnSyncIncomeBku" class="btn-toolbar btn-toolbar-info" onclick="syncIncomeCashBook()">
                <i class="ph ph-arrows-clockwise"></i>
                <span>Sinkronisasi</span>
            </button>
            <button class="btn-toolbar btn-toolbar-dark" onclick="openPreviewModal('BKU_PENDAPATAN')">
                <i class="ph ph-presentation"></i>
                <span>Preview & Unduh</span>
            </button>
        </div>
    </div>


    {{-- MAIN CONTENT --}}
    <div class="dashboard-box">
        <div class="table-toolbar">
            <div class="flex items-center gap-4">
                <div class="form-group-inline">
                    <select id="incomeBkuMonth" class="form-input" style="height:48px; width:180px; border-radius:12px;"
                        onchange="loadIncomeCashBook()">
                        <option value="">- Seluruh Bulan -</option>
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $m)
                            <option value="{{ $i + 1 }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-inline">
                    <select id="incomeBkuYear" class="form-input" style="height:48px; width:120px; border-radius:12px;"
                        onchange="loadIncomeCashBook()">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
            </div>
            <div class="table-search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchIncomeBku" class="table-search" placeholder="Cari di BKU..."
                    oninput="filterIncomeBkuTable()">
            </div>
        </div>

        <div class="table-container">
            <table id="tableIncomeBku" class="table universal-table">
                <thead>
                    <tr>
                        <th width="60" class="text-center">No</th>
                        <th width="120" class="text-center">Tanggal</th>
                        <th width="140" class="text-center">No Bukti</th>
                        <th>Uraian / Keterangan</th>
                        <th width="130" class="text-center">Sumber</th>
                        <th width="140" class="text-right">Penerimaan (Rp)</th>
                        <th width="140" class="text-right">Pengeluaran/Setor (Rp)</th>
                        <th width="150" class="text-right">Saldo Kas (Rp)</th>
                    </tr>
                </thead>
                <tbody id="tableIncomeBkuBody">
                    <tr>
                        <td colspan="8" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background:#f1f5f9; font-weight:800; border-top: 2px solid #cbd5e1;">
                        <td colspan="5" class="text-center">TOTAL MUTASI & SALDO AKHIR</td>
                        <td class="text-right" id="incomeBkuTotalPenerimaan">0</td>
                        <td class="text-right" id="incomeBkuTotalPengeluaran">0</td>
                        <td class="text-right" id="incomeBkuFinalSaldo" style="color:#0f172a">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- BKU SUMMARY FOOTER --}}
    <div id="ledgerFooterDetail" class="mt-8 pt-6 border-t-2 border-slate-200"
        style="margin-top: 32px; padding-top: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            <!-- Column 1: Cash Summary -->
            <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0;">
                <h4
                    style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
                    <i class="ph-fill ph-wallet" style="color: #6366f1;"></i>
                    Ringkasan Kas Penerimaan
                </h4>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; color: #64748b;">Jumlah Penerimaan Tunai s.d Periode ini</span>
                        <span id="footerTotalPenerimaan"
                            style="font-weight: 700; color: #0f172a; font-family: monospace;">Rp 0,00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; color: #64748b;">Jumlah Setoran ke Bank s.d Periode ini</span>
                        <span id="footerTotalPengeluaran"
                            style="font-weight: 700; color: #dc2626; font-family: monospace;">Rp 0,00</span>
                    </div>
                    <div style="height: 1px; background: #e2e8f0; margin: 8px 0;"></div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="footerLabelSaldoAkhir"
                            style="font-size: 13px; font-weight: 600; color: #1e293b;">Saldo Kas Bendahara Akhir
                            Bulan</span>
                        <span id="footerFinalSaldo"
                            style="font-weight: 800; color: #0f172a; font-size: 15px; font-family: monospace;">Rp
                            0,00</span>
                    </div>
                </div>
            </div>

            <!-- Column 2: Bank Summary -->
            <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0;">
                <h4
                    style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
                    <i class="ph-fill ph-bank" style="color: #0ea5e9;"></i>
                    Saldo Rekening Bank Akhir Bulan
                </h4>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div
                            style="width: 36px; height: 36px; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0;">
                            <span style="font-weight: 900; color: #cc7000; font-size: 10px;">BRK</span>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 13px; font-weight: 600; color: #334155;">Bank Riau Kepri
                                    Syariah</span>
                                <span id="footerBankBRK"
                                    style="font-weight: 700; color: #0f172a; font-family: monospace;">Rp 0,00</span>
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">146-01-01234</div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div
                            style="width: 36px; height: 36px; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0;">
                            <span style="font-weight: 900; color: #00a3ad; font-size: 10px;">BSI</span>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 13px; font-weight: 600; color: #334155;">Bank Syariah
                                    Indonesia</span>
                                <span id="footerBankBSI"
                                    style="font-weight: 700; color: #0f172a; font-family: monospace;">Rp 0,00</span>
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">7030374937</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof initIncomeCashBook === 'function') {
        initIncomeCashBook();
    }
</script>






