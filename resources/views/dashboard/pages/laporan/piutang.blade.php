<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Laporan Piutang
            </h2>
            <p>Rekapitulasi piutang berjalan dikelompokkan berdasarkan Perusahaan Penjamin</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Tahun Anggaran</label>
                    <select id="laporanTahun" class="filter-date-input" style="width: 100px;">
                        @php
                            $currentY = date('Y');
                            $sessionY = session('tahun_anggaran', $currentY);
                        @endphp
                        @for($y = $currentY; $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ $y == $sessionY ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
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
                    <tr style="background: #f8fafc;">
                        <th rowspan="2" class="text-center" style="vertical-align: middle;">Perusahaan</th>
                        <th colspan="4" class="text-center" style="border-bottom: 1px solid #e2e8f0;">Saldo Awal (Tahun
                            Lalu)</th>
                        <th colspan="4" class="text-center" style="border-bottom: 1px solid #e2e8f0;">Tahun Berjalan
                        </th>
                        <th rowspan="2" class="text-center" style="vertical-align: middle;">Pelunasan Total</th>
                        <th rowspan="2" class="text-center" style="vertical-align: middle;">Potongan Total</th>
                        <th rowspan="2" class="text-center" style="vertical-align: middle; color:#ef4444">Sisa 2025</th>
                        <th rowspan="2" class="text-center" style="vertical-align: middle; background: #f1f5f9;">Saldo
                            Akhir</th>
                    </tr>
                    <tr style="background: #f8fafc;">
                        <th class="text-center">Piutang</th>
                        <th class="text-center">Lunas</th>
                        <th class="text-center">Pot</th>
                        <th class="text-center">Adm</th>
                        <th class="text-center">Piutang</th>
                        <th class="text-center">Lunas</th>
                        <th class="text-center">Pot</th>
                        <th class="text-center">Adm</th>
                    </tr>
                </thead>
                <tbody id="laporanPiutangBody">
                    <!-- Dynamic -->
                </tbody>
            </table>
        </div>
    </div>
</div>