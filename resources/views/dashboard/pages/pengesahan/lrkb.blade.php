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
        <div class="table-container"><table class="laporan-table universal-table" id="lrkbTable">
            <thead>
                <tr>
                    <th class="text-center checkbox-col">No</th>
                    <th class="text-center">Triwulan</th>
                    <th class="text-center">Bulan</th>
                    <th class="text-center">Tahun</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Tgl Rekon</th>
                    <th class="text-center action-col">Aksi</th>
                </tr>
            </thead>
            <tbody id="lrkbBody">
                <tr>
                    <td colspan="7" class="text-center">Memuat data...</td>
                </tr>
            </tbody>
        </table></div>
    </div>
</div>

<script>
    if (typeof initLrkb === 'function') {
        initLrkb();
    }
</script>