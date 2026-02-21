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

    <!-- EXPENDITURE SUMMARY -->
    <style>
        /* Tighten page layout */
        .laporan {
            display: flex;
            flex-direction: column;
            gap: 16px !important;
        }

        .laporan-summary-container {
            display: flex;
            justify-content: center;
            margin-bottom: 8px;
        }

        .laporan-summary-container .dashboard-cards {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            width: 100%;
            max-width: 850px;
            gap: 16px;
        }
    </style>

    <div class="laporan-summary-container">
        <div class="dashboard-cards" id="laporanPengeluaranCards">
            <!-- Dynamic Cards: Pegawai, Barang/Jasa, Modal -->
        </div>
    </div>

    <div class="laporan-section">
        <div class="section-header">
            <h3>Rincian Pengeluaran Per Kode Rekening</h3>
        </div>
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 150px;">Sub Kode Rekening</th>
                        <th class="text-center">Nama Rekening</th>
                        <th class="text-center" style="text-align:right">Uang Persediaan</th>
                        <th class="text-center" style="text-align:right">Ganti Uang</th>
                        <th class="text-center" style="text-align:right">Langsung</th>
                        <th class="text-center" style="text-align:right">Total Pengeluaran</th>
                    </tr>
                </thead>
                <tbody id="laporanPengeluaranBody">
                    <tr>
                        <td colspan="6" class="text-center">Klik Tampilkan untuk memuat data.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>