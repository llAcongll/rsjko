@php
    $title = 'Buku Kas Umum (BKU)';
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

        <div class="flex justify-between items-center mt-4">
            <div id="ledgerPaginationInfo" style="font-size:13px; color:#64748b;">
                Menampilkan 0–0 dari 0 data
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