<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Laporan Realisasi Anggaran
            </h2>
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

    <div id="lraTableContainer">
        <!-- Tables will be injected here dynamically by renderAnggaran -->
        <div class="laporan-section">
            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 150px; vertical-align: middle;">Kode Rekening</th>
                            <th class="text-center" style="vertical-align: middle;">Uraian</th>
                            <th class="text-center" style="text-align:right; vertical-align: middle;">Target Anggaran
                            </th>
                            <th class="text-center" style="text-align:center">Realisasi (Lalu)</th>
                            <th class="text-center" style="text-align:center">Realisasi (Kini)</th>
                            <th class="text-center" style="text-align:center">Realisasi (Total)</th>
                            <th class="text-center" style="text-align:right; vertical-align: middle;">Selisih</th>
                            <th class="text-center" style="text-align:center; vertical-align: middle;">%</th>
                            <th class="text-center" style="width: 120px; vertical-align: middle;">Progres</th>
                        </tr>
                    </thead>
                    <tbody id="laporanAnggaranBody">
                        <tr>
                            <td colspan="9" class="text-center py-4 text-slate-400">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>