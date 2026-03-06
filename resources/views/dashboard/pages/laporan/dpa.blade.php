<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Laporan DPA
            </h2>
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
        .report-table.universal-table td {
            white-space: normal !important;
            vertical-align: top;
        }

        .report-table.universal-table th {
            white-space: nowrap !important;
        }
    </style>
    <div class="laporan-section">
        <div class="section-header">
            <h3>Rincian Komponen Anggaran</h3>
        </div>
        <div class="table-container">
            <table class="report-table universal-table" id="tableDPA">
                <thead>
                    <tr>
                        <th class="text-center sortable">Kode Rekening</th>
                        <th class="text-center sortable">Uraian Rekening / Komponen</th>
                        <th class="text-center sortable">Volume</th>
                        <th class="text-center sortable">Satuan</th>
                        <th class="text-center sortable">Tarif Satuan</th>
                        <th class="text-center sortable">Total</th>
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