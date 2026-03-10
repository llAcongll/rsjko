<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2 style="display: flex; align-items: center; gap: 10px;">
                <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
                    style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
                Laporan Arus Kas (LAK)
            </h2>
            <p>Laporan Arus Kas Berdasarkan SAP Akrual</p>
        </div>
        <div class="header-right">
            <div class="laporan-filter-group">
                <div class="filter-item">
                    <label>Periode</label>
                    <select id="lakFilterPeriode" class="filter-date-input" onchange="toggleLakPeriodInputs()">
                        <option value="Bulanan">Bulanan</option>
                        <option value="Triwulan">Triwulan</option>
                        <option value="Semester">Semester</option>
                        <option value="Tahunan">Tahunan</option>
                    </select>
                </div>

                <div class="filter-item" id="lakMonthContainer">
                    <label>Bulan</label>
                    <select id="lakFilterBulan" class="filter-date-input">
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $idx => $m)
                            <option value="{{ $idx + 1 }}" {{ date('n') == $idx + 1 ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-item" id="lakQuarterContainer" style="display:none;">
                    <label>Triwulan</label>
                    <select id="lakFilterTriwulan" class="filter-date-input">
                        <option value="1">Triwulan I</option>
                        <option value="2">Triwulan II</option>
                        <option value="3">Triwulan III</option>
                        <option value="4">Triwulan IV</option>
                    </select>
                </div>

                <div class="filter-item" id="lakSemesterContainer" style="display:none;">
                    <label>Semester</label>
                    <select id="lakFilterSemester" class="filter-date-input">
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

                <button class="btn-filter" onclick="loadLaporan('LAK')">
                    <i class="ph ph-magnifying-glass"></i>
                    <span>Tampilkan</span>
                </button>

                @if(auth()->user()->hasPermission('LAP_LAK_VIEW'))
                    <div class="filter-divider"></div>
                    <button class="btn-preview" onclick="openPreviewModal('LAK')">
                        <i class="ph ph-file-search"></i>
                        <span>Preview & Unduh</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div id="lakContent" class="laporan-body" style="margin-top: 20px;">
        <div class="empty-state"
            style="text-align: center; padding: 50px; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
            <i class="ph ph-files" style="font-size: 48px; color: #94a3b8;"></i>
            <p style="margin-top: 10px; color: #64748b;">Silakan pilih periode dan klik Tampilkan</p>
        </div>
    </div>
</div>

<script>
    function toggleLakPeriodInputs() {
        const period = document.getElementById('lakFilterPeriode').value;
        document.getElementById('lakMonthContainer').style.display = (period === 'Bulanan') ? 'block' : 'none';
        document.getElementById('lakQuarterContainer').style.display = (period === 'Triwulan') ? 'block' : 'none';
        document.getElementById('lakSemesterContainer').style.display = (period === 'Semester') ? 'block' : 'none';
    }
</script>






