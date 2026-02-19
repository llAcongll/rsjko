<div class="dashboard">

    {{-- HEADER --}}
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h2><i class="ph ph-chart-bar"></i> Anggaran Pengeluaran</h2>
            <p>Target dan realisasi anggaran pengeluaran tahunan</p>
        </div>

        <div class="dashboard-header-right">
            <div class="filter-group"
                style="display: flex; align-items: center; gap: 10px; background: #fff; padding: 4px 12px; border-radius: 12px; border: 1px solid #e2e8f0;">
                <label for="anggaranTahun" style="font-size: 13px; font-weight: 600; color: #64748b;">Tahun:</label>
                <select id="anggaranTahun" onchange="reloadAnggaran('PENGELUARAN')"
                    style="border: none; font-weight: 700; color: #1e293b; outline: none; cursor: pointer;">
                    @for($y = 2024; $y <= 2030; $y++)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    {{-- MAIN BOX --}}
    <div class="dashboard-box">
        <div class="anggaran-wrapper">
            <div class="anggaran-row header-row">
                <div class="col-kode">Kode Rekening</div>
                <div class="col-uraian">Uraian</div>
                <div class="col-anggaran">Anggaran (Rp)</div>
                <div class="col-realisasi">Realisasi (Rp)</div>
            </div>

            <div id="kodeRekeningTree"></div>
        </div>
    </div>

</div>