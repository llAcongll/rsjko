<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-handshake"></i> Laporan Kerjasama / MOU</h2>
            <p>Rekapitulasi pendapatan berdasarkan dokumen Kerjasama dan MOU</p>
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
                <button class="btn-filter" onclick="loadLaporan('MOU')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>
                @if(auth()->user()->hasPermission('LAPORAN_MOU') || auth()->user()->hasPermission('LAPORAN_VIEW'))
                    <button class="btn-preview" onclick="openPreviewModal('MOU')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="laporan-section">
        <div class="section-header">
            <h3>Pendapatan Per MOU / Instansi</h3>
        </div>
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Nama MOU / Instansi</th>
                        <th style="text-align:center">Trans</th>
                        <th style="text-align:right">Jasa RS</th>
                        <th style="text-align:right">Jasa Pelayanan</th>
                        <th style="text-align:right">Gross Total</th>
                        <th style="text-align:right">Potongan</th>
                        <th style="text-align:right">Adm Bank</th>
                        <th style="text-align:right">Total Netto</th>
                    </tr>
                </thead>
                <tbody id="laporanMouBody">
                    <!-- Dynamic -->
                </tbody>
            </table>
        </div>
    </div>
</div>