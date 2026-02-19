<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-chart-line"></i> Laporan Realisasi Anggaran</h2>
            <p id="lraDescription">Perbandingan Pencapaian Pendapatan terhadap Target Anggaran Pendapatan</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Dari Tanggal</label>
                    <input type="date" id="laporanStart" class="filter-date-input">
                </div>
                <div class="filter-item">
                    <label>Sampai Tanggal</label>
                    <input type="date" id="laporanEnd" class="filter-date-input">
                </div>
                <div class="filter-item">
                    <label>Kategori</label>
                    <select id="lraCategory" class="filter-date-input" style="width: 140px;">
                        <option value="SEMUA" selected>SEMUA</option>
                        <option value="PENDAPATAN">PENDAPATAN</option>
                        <option value="PENGELUARAN">PENGELUARAN</option>
                    </select>
                </div>
                <button class="btn-filter" onclick="loadLaporan('ANGGARAN')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>
                @if(auth()->user()->hasPermission('LAPORAN_ANGGARAN') || auth()->user()->hasPermission('LAPORAN_VIEW'))
                    <button class="btn-preview" onclick="openPreviewModal('ANGGARAN')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div id="lraCardsContainer">
        <!-- Dynamic Cards -->
    </div>

    <div class="laporan-section">
        <div class="section-header">
            <h3>Rincian Realisasi Per Kode Rekening</h3>
        </div>
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 150px; vertical-align: middle;">Kode Rekening</th>
                        <th style="vertical-align: middle;">Uraian</th>
                        <th style="text-align:right; vertical-align: middle;">Target Anggaran</th>
                        <th style="text-align:center">Realisasi (Lalu)</th>
                        <th style="text-align:center">Realisasi (Kini)</th>
                        <th style="text-align:center">Realisasi (Total)</th>
                        <th style="text-align:right; vertical-align: middle;">Selisih</th>
                        <th style="text-align:center; vertical-align: middle;">%</th>
                        <th style="width: 120px; vertical-align: middle;">Progres</th>
                    </tr>
                </thead>
                <tbody id="laporanAnggaranBody">
                    <!-- Dynamic -->
                </tbody>
            </table>
        </div>
    </div>
</div>