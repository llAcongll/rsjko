<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-scales"></i> Laporan Rekonsiliasi</h2>
            <p>Perbandingan data Rekening Koran (Bank) vs Modul Pendapatan</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Dari</label>
                    <input type="date" id="laporanStart" class="filter-date-input">
                </div>
                <div class="filter-item">
                    <label>Sampai</label>
                    <input type="date" id="laporanEnd" class="filter-date-input">
                </div>
                <button class="btn-filter" onclick="loadLaporan('REKON')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Filter</span>
                </button>
            </div>
        </div>
    </div>

    <!-- RECONCILIATION SUMMARY -->
    <div class="dashboard-cards" style="margin-bottom: 24px; grid-template-columns: repeat(3, 1fr);">
        <div class="dash-card blue">
            <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
            <div class="dash-card-content">
                <span class="label">Total Bank</span>
                <h3 id="rekonTotalBank">Rp 0</h3>
            </div>
        </div>
        <div class="dash-card purple">
            <div class="dash-card-icon"><i class="ph ph-hand-coins"></i></div>
            <div class="dash-card-content">
                <span class="label">Total Pendapatan</span>
                <h3 id="rekonTotalPend">Rp 0</h3>
            </div>
        </div>
        <div class="dash-card orange">
            <div class="dash-card-icon"><i class="ph ph-warning"></i></div>
            <div class="dash-card-content">
                <span class="label">Net Selisih</span>
                <h3 id="rekonTotalDiff">Rp 0</h3>
            </div>
        </div>
    </div>

    <!-- RECONCILIATION SECTION -->
    <div class="laporan-section rekon-section">
        <div class="table-responsive">
            <table class="report-table" id="laporanRekonTable">
                <thead>
                    <tr>
                        <th style="width: 120px;">Tanggal</th>
                        <th style="text-align:right">Bank (Kredit)</th>
                        <th style="text-align:right">Modul Netto</th>
                        <th style="text-align:right">Selisih Harian</th>
                        <th style="text-align:right">Selisih Kumulatif</th>
                        <th style="text-align:center; width: 120px;">Status</th>
                    </tr>
                </thead>
                <tbody id="laporanRekonBody">
                    <tr>
                        <td colspan="6" style="text-align:center">⏳ Memuat data rekon...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>