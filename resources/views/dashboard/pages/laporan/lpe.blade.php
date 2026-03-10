<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Laporan Perubahan Ekuitas (LPE)
            </h2>
            <p>Laporan Perubahan Modal Berdasarkan SAP Akrual</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Periode</label>
                    <select id="lpePeriode" class="filter-date-input" onchange="toggleLpeFilters()">
                        <option value="Tahunan">Tahunan</option>
                        <option value="Semester">Semester</option>
                        <option value="Triwulan">Triwulan</option>
                        <option value="Bulanan">Bulanan</option>
                    </select>
                </div>

                <div class="filter-item" id="lpeMonthContainer" style="display: none;">
                    <label>Bulan</label>
                    <select id="lpeBulan" class="filter-date-input">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-item" id="lpeQuarterContainer" style="display: none;">
                    <label>Triwulan</label>
                    <select id="lpeTriwulan" class="filter-date-input">
                        <option value="1">Triwulan I</option>
                        <option value="2">Triwulan II</option>
                        <option value="3">Triwulan III</option>
                        <option value="4">Triwulan IV</option>
                    </select>
                </div>

                <div class="filter-item" id="lpeSemesterContainer" style="display: none;">
                    <label>Semester</label>
                    <select id="lpeSemester" class="filter-date-input">
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

                <button class="btn-filter" onclick="loadLaporan('LPE')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>

                @if(auth()->user()->hasPermission('LAP_LPE_VIEW'))
                    <div class="filter-divider"></div>
                    <button class="btn-preview" onclick="openPreviewModal('LPE')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div id="lpeContent">
        <div style="text-align: center; padding: 100px 0; color: #94a3b8;">
            <i class="ph ph-trend-up" style="font-size: 48pt; opacity: 0.2; margin-bottom: 16px; display: block;"></i>
            <p>Silakan pilih periode dan klik Tampilkan</p>
        </div>
    </div>

    <script>
        function toggleLpeFilters() {
            const p = document.getElementById('lpePeriode').value;
            document.getElementById('lpeMonthContainer').style.display = (p === 'Bulanan') ? 'block' : 'none';
            document.getElementById('lpeQuarterContainer').style.display = (p === 'Triwulan') ? 'block' : 'none';
            document.getElementById('lpeSemesterContainer').style.display = (p === 'Semester') ? 'block' : 'none';
        }
    </script>
</div>






