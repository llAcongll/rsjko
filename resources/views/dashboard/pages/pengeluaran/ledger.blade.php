@php
    $title = 'Buku Kas Umum Pengeluaran (BKU)';
@endphp

<div class="page-container">
    {{-- HEADER --}}
    <div class="page-header">
        <div class="page-header-left">
            <h2><i class="ph ph-book-open"></i> {{ $title }}</h2>
            <p>Laporan transaksi kas bendahara otomatis</p>
        </div>

        <div class="page-header-right">
            <button id="btnSyncLedger" class="btn-toolbar btn-toolbar-info" onclick="syncLedger()">
                <i class="ph ph-arrows-clockwise"></i>
                <span>Sinkronisasi</span>
            </button>
            <button class="btn-toolbar btn-toolbar-dark" onclick="openPreviewModal('BKU')">
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
                    <select id="ledgerMonth" class="form-input" style="height:48px; width:180px; border-radius:12px;"
                        onchange="loadLedger()">
                        <option value="">Semua Bulan</option>
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $m)
                            <option value="{{ $i + 1 }}">{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-inline">
                    <select id="ledgerYear" class="form-input" style="height:48px; width:120px; border-radius:12px;"
                        onchange="loadLedger()">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
            </div>
            <div class="table-search-wrapper">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="searchLedger" class="table-search" placeholder="Cari di BKU..."
                    oninput="filterLedgerTable()">
            </div>
        </div>

        <div class="table-container">
            <table id="tableLedger" class="table universal-table">
                <thead>
                    <tr>
                        <th width="60" class="text-center checkbox-col">No</th>
                        <th width="120" class="text-center">Tanggal</th>
                        <th width="140" class="text-center">No Bukti</th>
                        <th>Uraian / Referensi</th>
                        <th width="140" class="text-center">Kode Rek</th>
                        <th width="140" class="text-right">Transfer Penerimaan</th>
                        <th width="140" class="text-right">Pengajuan SP2D</th>
                        <th width="140" class="text-right">Realisasi</th>
                        <th width="140" class="text-right">Saldo Dana</th>
                        <th width="140" class="text-right">Saldo RK</th>
                        <th width="140" class="text-right">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody id="tableLedgerBody">
                    <tr>
                        <td colspan="11" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
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
                        Ringkasan Kas Bendahara
                    </h4>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 13px; color: #64748b;">Jumlah Penerimaan Tunai s.d Periode
                                ini</span>
                            <span id="footerTotalPenerimaan"
                                style="font-weight: 700; color: #0f172a; font-family: monospace;">Rp 0,00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 13px; color: #64748b;">Jumlah Setoran ke Bank s.d Periode ini</span>
                            <span id="footerTotalPengeluaran"
                                style="font-weight: 700; color: #0f172a; font-family: monospace;">Rp 0,00</span>
                        </div>
                        <div style="height: 1px; background: #e2e8f0; margin: 8px 0;"></div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="footerLabelSaldoAkhir"
                                style="font-size: 13px; font-weight: 600; color: #1e293b;">Saldo Kas Bendahara Akhir
                                Bulan</span>
                            <span id="footerFinalSaldo"
                                style="font-weight: 800; color: #6366f1; font-size: 15px; font-family: monospace;">Rp
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

        <div class="flex justify-between items-center mt-4">
            <div id="ledgerPaginationInfo" style="font-size:13px; color:#64748b;">
                Menampilkan 0-0 dari 0 data
            </div>
            <div class="flex items-center gap-2">
                <button class="btn-aksi" id="btnLedgerPrev" onclick="changeLedgerPage(-1)">
                    <i class="ph ph-caret-left"></i>
                </button>
                <span id="ledgerPageIndicator"
                    style="font-weight:600; font-size:14px; min-width:80px; text-align:center;">1 / 1</span>
                <button class="btn-aksi" id="btnLedgerNext" onclick="changeLedgerPage(1)">
                    <i class="ph ph-caret-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof initLedger === 'function') {
        initLedger();
    }
</script>





