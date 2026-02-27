@php
    $title = 'LRKB (Laporan Rekonsiliasi Kas Bendahara)';
@endphp

<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-scales"></i> Rekonsiliasi Kas (LRKB)</h2>
            <p>Rekonsiliasi Kas Bank & Tunai Per Triwulan / Bulan</p>
        </div>
        <div class="header-right">
            <button class="btn-filter" onclick="showNewLrkbModal()">
                <i class="ph ph-plus"></i>
                <span>Tambah Periode</span>
            </button>
        </div>
    </div>

    <div class="dashboard-box">
        <table class="laporan-table" id="lrkbTable">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th class="text-center" style="width: 120px;">Triwulan</th>
                    <th class="text-center" style="width: 120px;">Bulan</th>
                    <th class="text-center" style="width: 100px;">Tahun</th>
                    <th class="text-center" style="width: 150px;">Status</th>
                    <th class="text-center" style="width: 180px;">Tgl Rekon</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="lrkbBody">
                <tr>
                    <td colspan="7" class="text-center">Memuat data...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    if (typeof initLrkb === 'function') {
        initLrkb();
    }
</script>