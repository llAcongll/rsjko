<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-article"></i> Laporan DPA</h2>
            <p>Rincian Dokumen Pelaksanaan Anggaran (DPA) tahun berjalan</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Tahun Anggaran</label>
                    <input type="text" id="laporanTahun" class="filter-date-input"
                        value="{{ session('tahun_anggaran') }}" readonly
                        style="background: #f1f5f9; cursor: not-allowed; width: 80px; text-align: center;">
                </div>
                <button class="btn-filter" onclick="loadLaporan('DPA')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>
                <div class="filter-divider" style="width: 1px; height: 24px; background: #e2e8f0; margin: 0 8px;"></div>
                @if(auth()->user()->hasPermission('LAPORAN_ANGGARAN') || auth()->user()->hasPermission('LAPORAN_VIEW'))
                    <button class="btn-preview" onclick="openPreviewModal('DPA')">
                        <i class="ph ph-eye"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <style>
        .report-table td {
            white-space: normal !important;
            vertical-align: top;
        }
    </style>
    <div class="laporan-section">
        <div class="section-header">
            <h3>Rincian Komponen Anggaran</h3>
        </div>
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 150px;">Kode Rekening</th>
                        <th class="text-center">Uraian Rekening / Komponen</th>
                        <th class="text-center" style="width: 80px;">Volume</th>
                        <th class="text-center" style="width: 100px;">Satuan</th>
                        <th class="text-center" style="width: 150px;">Tarif Satuan</th>
                        <th class="text-center" style="width: 180px;">Total</th>
                    </tr>
                </thead>
                <tbody id="laporanDPABody">
                    <tr>
                        <td colspan="6" class="text-center">Klik Tampilkan untuk memuat data.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>