@php
    $title = 'SP3BP (Surat Perintah Pengesahan Pendapatan & Belanja)';
@endphp

<div class="laporan">
    <div class="laporan-header">
        <div class="header-left">
            <h2><i class="ph ph-file-text"></i> Pengesahan SP3BP</h2>
            <p>Kelola Pengesahan Pendapatan & Belanja (Bulanan)</p>
        </div>
        <div class="header-right">
            <button class="btn-filter" onclick="showNewSp3bpModal()">
                <i class="ph ph-plus"></i>
                <span>Tambah Periode</span>
            </button>
        </div>
    </div>

    <div class="dashboard-box">
        <table class="laporan-table" id="sp3bpTable">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th class="text-center" style="width: 150px;">Periode</th>
                    <th class="text-center" style="width: 100px;">Tahun</th>
                    <th class="text-center" style="width: 150px;">Status</th>
                    <th class="text-center" style="width: 200px;">Tgl Pengesahan</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="sp3bpBody">
                <tr>
                    <td colspan="6" class="text-center">Memuat data...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<script>
    if (typeof initSp3bp === 'function') {
        initSp3bp();
    }
</script>