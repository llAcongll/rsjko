<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Laporan Rekonsiliasi BKAD
            </h2>
            <p>Berita Acara Rekonsiliasi Data Keuangan (Bank vs Sistem)</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Periode</label>
                    <select id="rekonFilterPeriode" class="filter-date-input" onchange="toggleRekonPeriodInputs()">
                        <option value="Bulanan">Bulanan</option>
                        <option value="Triwulan">Triwulan</option>
                        <option value="Semester">Semester</option>
                        <option value="Tahunan">Tahunan</option>
                    </select>
                </div>

                <div class="filter-item" id="rekonMonthContainer">
                    <label>Bulan</label>
                    <select id="rekonFilterBulan" class="filter-date-input">
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $idx => $m)
                            <option value="{{ $idx + 1 }}" {{ date('n') == $idx + 1 ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-item" id="rekonQuarterContainer" style="display:none;">
                    <label>Triwulan</label>
                    <select id="rekonFilterTriwulan" class="filter-date-input">
                        <option value="1">Triwulan I</option>
                        <option value="2">Triwulan II</option>
                        <option value="3">Triwulan III</option>
                        <option value="4">Triwulan IV</option>
                    </select>
                </div>

                <div class="filter-item" id="rekonSemesterContainer" style="display:none;">
                    <label>Semester</label>
                    <select id="rekonFilterSemester" class="filter-date-input">
                        <option value="1">Semester I</option>
                        <option value="2">Semester II</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label>Tahun</label>
                    <select id="laporanTahun" class="filter-date-input">
                        @php $curr = session('tahun_anggaran', date('Y')); @endphp
                        @for($y = $curr - 1; $y <= $curr + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $curr ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <button class="btn-filter" onclick="loadLaporan('REKON')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>

                @if(auth()->user()->hasPermission('LAP_REKON_VIEW'))
                    <div class="filter-divider"></div>
                    <button class="btn-preview" onclick="openPreviewModal('REKON')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- RECONCILIATION SUMMARY -->
    <style>
        .laporan {
            display: flex;
            flex-direction: column;
            gap: 20px !important;
        }

        .dashboard-cards {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            width: 100%;
            max-width: 850px;
            gap: 16px;
            margin: 0 auto;
        }

        .section-title {
            margin-bottom: 16px;
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            border-left: 4px solid #2563eb;
            padding-left: 12px;
        }

        .table-container {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
    </style>

    <div class="rekon-summary-container">
        <div class="dashboard-cards">
            <div class="dash-card blue">
                <div class="dash-card-icon"><i class="ph ph-bank"></i></div>
                <div class="dash-card-content">
                    <span class="label">Total Bank</span>
                    <h3 id="rekonTotalBank">Rp 0</h3>
                    <small>Histori Rekening Koran</small>
                </div>
            </div>
            <div class="dash-card purple">
                <div class="dash-card-icon"><i class="ph ph-hand-coins"></i></div>
                <div class="dash-card-content">
                    <span class="label">Total Pendapatan</span>
                    <h3 id="rekonTotalPend">Rp 0</h3>
                    <small>Berdasarkan Modul</small>
                </div>
            </div>
            <div class="dash-card orange">
                <div class="dash-card-icon"><i class="ph ph-warning"></i></div>
                <div class="dash-card-content">
                    <span class="label">Net Selisih</span>
                    <h3 id="rekonTotalDiff">Rp 0</h3>
                    <small>Selisih Akumulasi</small>
                </div>
            </div>
        </div>
    </div>

    <!-- BAGIAN A - REKAPITULASI PENDAPATAN -->
    <div class="laporan-section recap-section">
        <h4 class="section-title">BAGIAN A - DATA KAS BENDAHARA PENERIMAAN</h4>
        <div class="table-container">
            <table class="report-table universal-table" id="rekonRecapTable">
                <thead>
                    <tr>
                        <th class="text-center">Bulan</th>
                        <th class="text-right">Pendapatan Sistem</th>
                        <th class="text-right">Rekening Koran Bank</th>
                        <th class="text-right">Selisih</th>
                        <th class="text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody id="rekonRecapBody">
                    <tr>
                        <td colspan="5" class="text-center">Klik tampilkan untuk memuat data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- BAGIAN B - DATA SALDO REKENING KORAN -->
    <div class="laporan-section bank-balance-section">
        <h4 class="section-title">BAGIAN B - DATA SALDO REKENING KORAN</h4>
        <div class="table-container">
            <table class="report-table universal-table" id="rekonBankBalanceTable">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-left">Nama Bank</th>
                        <th class="text-left">Nama Rekening</th>
                        <th class="text-center">No Rekening</th>
                        <th class="text-right">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody id="rekonBankBalanceBody">
                    <tr>
                        <td colspan="5" class="text-center">Klik tampilkan untuk memuat data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- BAGIAN C - ANALISIS SELISIH TRANSAKSI -->
    <div class="laporan-section rekon-section">
        <h4 class="section-title">BAGIAN C - ANALISIS SELISIH TRANSAKSI</h4>
        <div class="table-container">
            <table class="report-table universal-table" id="laporanRekonTable">
                <thead>
                    <tr>
                        <th class="text-center sortable">Tanggal</th>
                        <th class="text-center sortable">Nominal Sistem</th>
                        <th class="text-center sortable">Nominal Bank</th>
                        <th class="text-center sortable">Selisih</th>
                        <th class="text-left sortable">Status & Keterangan</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="laporanRekonBody">
                    <tr>
                        <td colspan="6" class="text-center">⌛ Memuat data rekon...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>






