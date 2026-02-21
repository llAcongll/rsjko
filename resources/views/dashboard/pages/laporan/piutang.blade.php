<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-invoice"></i> Laporan Piutang</h2>
            <p>Rekapitulasi piutang berjalan dikelompokkan berdasarkan Perusahaan Penjamin</p>
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
                <button class="btn-filter" onclick="loadLaporan('PIUTANG')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>
                @if(auth()->user()->hasPermission('LAPORAN_PIUTANG') || auth()->user()->hasPermission('LAPORAN_VIEW'))
                    <button class="btn-preview" onclick="openPreviewModal('PIUTANG')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="laporan-main-cards">
        <div class="laporan-card highlight-orange">
            <div class="card-icon"><i class="ph ph-money"></i></div>
            <div class="card-info">
                <h3>TOTAL PIUTANG</h3>
                <span id="totalPiutangReport">Rp 0</span>
            </div>
        </div>
        <div class="laporan-card highlight-red">
            <div class="card-icon"><i class="ph ph-scissors"></i></div>
            <div class="card-info">
                <h3>TOTAL POTONGAN</h3>
                <span id="totalPotonganPiutangReport">Rp 0</span>
            </div>
        </div>
        <div class="laporan-card highlight-purple">
            <div class="card-icon"><i class="ph ph-bank"></i></div>
            <div class="card-info">
                <h3>TOTAL ADM BANK</h3>
                <span id="totalAdmBankPiutangReport">Rp 0</span>
            </div>
        </div>
        <div class="laporan-card highlight-blue">
            <div class="card-icon"><i class="ph ph-hand-coins"></i></div>
            <div class="card-info">
                <h3>TOTAL DITERIMA</h3>
                <span id="totalDiterimaPiutangReport">Rp 0</span>
            </div>
        </div>
    </div>

    <div class="laporan-section">
        <div class="section-header">
            <h3>Rincian Piutang Per Perusahaan</h3>
        </div>
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th class="text-center">Nama Perusahaan</th>
                        <th class="text-center" style="text-align:right">Jumlah Piutang</th>
                        <th class="text-center" style="text-align:right">Potongan</th>
                        <th class="text-center" style="text-align:right">Adm Bank</th>
                        <th class="text-center" style="text-align:right">Total Dibayar</th>
                        <th class="text-center" style="text-align:right">Sisa Piutang</th>
                    </tr>
                </thead>
                <tbody id="laporanPiutangBody">
                    <!-- Dynamic -->
                </tbody>
            </table>
        </div>
    </div>
</div>