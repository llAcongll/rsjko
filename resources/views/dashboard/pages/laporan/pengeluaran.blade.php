<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-hand-holding-money"></i> Laporan Pengeluaran</h2>
            <p>Rekapitulasi realisasi belanja berdasarkan periode dan kategori</p>
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
                <button class="btn-filter" onclick="loadLaporan('PENGELUARAN')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>
                <div class="filter-divider" style="width: 1px; height: 24px; background: #e2e8f0; margin: 0 8px;"></div>
                @if(auth()->user()->hasPermission('LAPORAN_PENGELUARAN') || auth()->user()->hasPermission('LAPORAN_VIEW'))
                    <button class="btn-preview" onclick="openPreviewModal('PENGELUARAN')">
                        <i class="ph ph-eye"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Row 1: Expenditure Type Cards -->
    <div class="laporan-main-cards" id="laporanPengeluaranCards">
        <!-- Dynamic Cards: Pegawai, Barang/Jasa, Modal -->
    </div>

    <div class="laporan-section">
        <div class="section-header">
            <h3>Rincian Pengeluaran Per Kode Rekening</h3>
        </div>
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 150px;">Sub Kode Rekening</th>
                        <th>Nama Rekening</th>
                        <th style="text-align:right">Total Pengeluaran</th>
                        <th style="text-align:center">Jumlah Transaksi</th>
                    </tr>
                </thead>
                <tbody id="laporanPengeluaranBody">
                    <tr>
                        <td colspan="4" class="text-center">Klik Tampilkan untuk memuat data.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>