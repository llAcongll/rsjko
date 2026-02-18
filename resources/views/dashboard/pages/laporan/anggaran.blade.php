<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-chart-line"></i> Laporan Realisasi Anggaran</h2>
            <p>Perbandingan Pencapaian Pendapatan terhadap Target Anggaran Pendapatan</p>
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
    <div class="laporan-main-cards anggaran-summary">
        <div class="laporan-card highlight-blue">
            <div class="card-icon"><i class="ph ph-target"></i></div>
            <div class="card-info">
                <h3>TARGET ANGGARAN</h3>
                <span id="totalTargetAnggaran" class="big">Rp 0</span>
                <p>Estimasi pendapatan</p>
            </div>
        </div>
        <div class="laporan-card highlight-green">
            <div class="card-icon"><i class="ph ph-trend-up"></i></div>
            <div class="card-info">
                <h3>REALISASI</h3>
                <span id="totalRealisasiAnggaran" class="big">Rp 0</span>
                <p>Pendapatan terhimpun</p>
            </div>
        </div>
        <div class="laporan-card highlight-orange">
            <div class="card-icon"><i class="ph ph-percent"></i></div>
            <div class="card-info">
                <h3>CAPAIAN</h3>
                <span id="totalPersentaseAnggaran" class="big">0%</span>
                <p>Dari total target</p>
            </div>
        </div>
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