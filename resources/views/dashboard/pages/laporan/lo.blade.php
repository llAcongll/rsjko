<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Laporan Operasional (LO)
            </h2>
            <p>Laporan Kinerja Keuangan (Surplus / Defisit) Berdasarkan SAP Akrual</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Periode</label>
                    <select id="loPeriode" class="filter-date-input" onchange="toggleLoFilters()">
                        <option value="Tahunan">Tahunan</option>
                        <option value="Semester">Semester</option>
                        <option value="Triwulan">Triwulan</option>
                        <option value="Bulanan">Bulanan</option>
                    </select>
                </div>

                <div class="filter-item" id="loMonthContainer" style="display: none;">
                    <label>Bulan</label>
                    <select id="loBulan" class="filter-date-input">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-item" id="loQuarterContainer" style="display: none;">
                    <label>Triwulan</label>
                    <select id="loTriwulan" class="filter-date-input">
                        <option value="1">Triwulan I</option>
                        <option value="2">Triwulan II</option>
                        <option value="3">Triwulan III</option>
                        <option value="4">Triwulan IV</option>
                    </select>
                </div>

                <div class="filter-item" id="loSemesterContainer" style="display: none;">
                    <label>Semester</label>
                    <select id="loSemester" class="filter-date-input">
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

                <button class="btn-filter" onclick="loadLaporan('LO')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>

                @if(auth()->user()->hasPermission('LAP_LO_VIEW'))
                    <div class="filter-divider"></div>
                    <button class="btn-preview" onclick="openPreviewModal('LO')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div id="loContent">
        <div style="text-align: center; padding: 100px 0; color: #94a3b8;">
            <i class="ph ph-file-text" style="font-size: 48pt; opacity: 0.2; margin-bottom: 16px; display: block;"></i>
            <p>Silakan pilih periode dan klik Tampilkan</p>
        </div>
    </div>

    <script>
        function toggleLoFilters() {
            const p = document.getElementById('loPeriode').value;
            document.getElementById('loMonthContainer').style.display = (p === 'Bulanan') ? 'block' : 'none';
            document.getElementById('loQuarterContainer').style.display = (p === 'Triwulan') ? 'block' : 'none';
            document.getElementById('loSemesterContainer').style.display = (p === 'Semester') ? 'block' : 'none';
        }
    </script>
</div>






