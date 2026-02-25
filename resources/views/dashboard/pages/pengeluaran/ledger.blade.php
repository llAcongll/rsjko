@php
    $title = 'Buku Kas Umum (BKU)';
@endphp

<div class="dashboard">
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-book-open"></i> {{ $title }}</h2>
            <p>Laporan transaksi kas bendahara otomatis</p>
        </div>

        <div class="dashboard-header-right">
            <button id="btnSyncLedger" class="btn-tambah-data" style="background:#059669;" onclick="syncLedger()">
                <i class="ph ph-arrows-clockwise"></i>
                <span>Sinkronisasi BKU</span>
            </button>
            <button class="btn-tambah-data" style="background:#0f172a;" onclick="openPreviewModal('BKU')">
                <i class="ph ph-presentation"></i>
                <span>Preview & Unduh</span>
            </button>
        </div>
    </div>

    <div class="dashboard-box">
        <div class="dashboard-box-header" style="display:flex; gap:16px;">
            <div class="form-group" style="margin:0;">
                <label style="font-size:11px;">Bulan</label>
                <select id="ledgerMonth" class="form-input" style="height:38px; width:150px;" onchange="loadLedger()">
                    <option value="">Semua Bulan</option>
                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $m)
                        <option value="{{ $i + 1 }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:11px;">Tahun</label>
                <select id="ledgerYear" class="form-input" style="height:38px; width:100px;" onchange="loadLedger()">
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table id="tableLedger">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="100" class="text-center">Tanggal</th>
                        <th width="120" class="text-center">No Bukti</th>
                        <th>Uraian / Referensi</th>
                        <th width="120" class="text-center">Kode Rek</th>
                        <th width="120" class="text-right">Transfer Penerimaan</th>
                        <th width="120" class="text-right">Pengajuan SP2D</th>
                        <th width="120" class="text-right">Realisasi</th>
                        <th width="120" class="text-right">Saldo Dana</th>
                        <th width="120" class="text-right">Saldo Rekening Koran</th>
                        <th width="120" class="text-right">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody id="tableLedgerBody">
                    <tr>
                        <td colspan="7" class="text-center">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="dashboard-box-footer"
            style="padding:16px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
            <div id="ledgerPaginationInfo" style="font-size:13px; color:#64748b;">
                Menampilkan 0–0 dari 0 data
            </div>
            <div class="pagination-controls" style="display:flex; gap:8px; align-items:center;">
                <button class="btn-aksi" id="btnLedgerPrev" onclick="changeLedgerPage(-1)"
                    style="padding:4px 12px; border:1px solid #e2e8f0;"><i class="ph ph-caret-left"></i></button>
                <span id="ledgerPageIndicator"
                    style="font-weight:600; font-size:13px; min-width:60px; text-align:center;">1 / 1</span>
                <button class="btn-aksi" id="btnLedgerNext" onclick="changeLedgerPage(1)"
                    style="padding:4px 12px; border:1px solid #e2e8f0;"><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof initLedger === 'function') {
        initLedger();
    }
</script>